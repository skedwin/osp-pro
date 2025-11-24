<?php

namespace App\Http\Controllers;

use App\Support\NcKenyaProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt against the remote API.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $tokenBaseUrl = rtrim(config('services.nckenya.token_base_url', 'https://api.nckenya.go.ke'), '/');
        $tokenEndpoint = config('services.nckenya.token_endpoint', '/login');
        $tokenUrl = $tokenBaseUrl . '/' . ltrim($tokenEndpoint, '/');

        $clientUsername = config('services.nckenya.client_username');
        $clientKey = config('services.nckenya.client_key');

        if (empty($clientUsername) || empty($clientKey)) {
            Log::error('NC Kenya client credentials missing from configuration.');

            throw ValidationException::withMessages([
                'username' => 'Service credentials are not configured. Please contact the administrator.',
            ]);
        }

        try {
            $tokenResponse = Http::acceptJson()->post($tokenUrl, [
                'username' => $clientUsername,
                'key' => $clientKey,
            ]);
        } catch (\Throwable $th) {
            Log::error('Failed to reach NC Kenya API login endpoint', [
                'url' => $tokenUrl,
                'error' => $th->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'username' => 'Unable to reach the NC Kenya API. Please try again later.',
            ]);
        }

        if ($tokenResponse->failed()) {
            $message = $tokenResponse->json('message') ?? 'Invalid credentials or API error.';

            throw ValidationException::withMessages([
                'username' => $message,
            ]);
        }

        $tokenPayload = $tokenResponse->json();

        // Robust token extraction: search common keys recursively for a non-empty token string.
        // Also accept tokens returned under 'message' if they look like a JWT (three dot-separated parts).
        $searchKeys = ['access_token', 'token', 'auth_token', 'message'];
        $jwtPattern = '/^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+$/';

        $finder = function ($data) use (&$finder, $searchKeys, $jwtPattern) {
            if (!is_array($data)) return null;
            foreach ($data as $k => $v) {
                $lk = strtolower($k);
                if (in_array($lk, $searchKeys, true)) {
                    if (is_string($v) && trim($v) !== '') {
                        // If key is 'message', ensure the value looks like a JWT before accepting
                        if ($lk === 'message') {
                            if (preg_match($jwtPattern, $v)) {
                                return $v;
                            }
                            // skip non-JWT message values (could be an error string)
                        } else {
                            return $v;
                        }
                    }
                }
                if (is_array($v)) {
                    $found = $finder($v);
                    if ($found) return $found;
                }
            }
            return null;
        };

        $token = $finder($tokenPayload);

        if (!is_string($token) || trim($token) === '') {
            Log::warning('NC Kenya API login succeeded but no usable token found in payload', [
                'status' => $tokenResponse->status(),
                'payload' => $tokenPayload,
            ]);

            throw ValidationException::withMessages([
                'username' => 'Login failed: no access token returned by the authentication service.',
            ]);
        }

        $ssoBaseUrl = rtrim(config('services.nckenya.sso_base_url', 'https://api.nckenya.go.ke'), '/');
        $ssoEndpoint = config('services.nckenya.sso_endpoint', '/single-sign-on');
        $ssoUrl = $ssoBaseUrl . '/' . ltrim($ssoEndpoint, '/');

        try {
            $ssoResponse = Http::withToken($token)
                ->acceptJson()
                ->post($ssoUrl, [
                    'username' => $credentials['username'],
                    'password' => $credentials['password'],
                ]);
        } catch (\Throwable $th) {
            Log::error('Failed to complete NC Kenya single sign-on request', [
                'url' => $ssoUrl,
                'error' => $th->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'username' => 'Authenticated but unable to complete single sign-on. Please try again later.',
            ]);
        }

        if ($ssoResponse->failed()) {
            $message = $ssoResponse->json('message')
                ?? $ssoResponse->json('error')
                ?? 'Authenticated token was rejected during single sign-on.';

            Log::warning('NC Kenya SSO rejected authenticated request', [
                'status' => $ssoResponse->status(),
                'body' => $ssoResponse->json(),
            ]);

            throw ValidationException::withMessages([
                'username' => $message,
            ]);
        }

        $ssoPayload = $ssoResponse->json();

        // Some APIs return the identity bundle under a 'message' key (or similar).
        // If so, and it's an array, prefer that inner payload for validation and session storage.
        if (is_array($ssoPayload) && isset($ssoPayload['message']) && is_array($ssoPayload['message'])) {
            $ssoPayload = $ssoPayload['message'];
        }

        // Basic validation of SSO payload to ensure authentication really succeeded
        $validSso = is_array($ssoPayload) && (
            data_get($ssoPayload, 'index_id') || data_get($ssoPayload, 'IndexNo') || data_get($ssoPayload, 'IdNumber') || data_get($ssoPayload, 'Name') || data_get($ssoPayload, 'profile') || data_get($ssoPayload, 'user')
        );

        if (!$validSso) {
            Log::warning('SSO response did not contain expected identity fields', [
                'status' => $ssoResponse->status(),
                'body' => $ssoPayload,
            ]);

            throw ValidationException::withMessages([
                'username' => 'Your username or password is invalid. Please try again.',
            ]);
        }

        // Store session only after token and SSO payload have been validated
        $request->session()->put('api_token', $token);
        $request->session()->put('api_client_username', $clientUsername);
        $request->session()->put('api_user', data_get($tokenPayload, 'user'));
        $request->session()->put('login_payload', $tokenPayload);
        $request->session()->put('sso_payload', $ssoPayload);
        $this->storeProfileFromSsoPayload($request, $ssoPayload);

        $request->session()->forget('last_api_response');

        return redirect()
            ->route('portal.dashboard')
            ->with('status', 'Successfully authenticated and signed in to the NC Kenya portal.');
    }

    protected function storeProfileFromSsoPayload(Request $request, array $ssoPayload): void
    {
        $normalized = NcKenyaProfile::fromPayload($ssoPayload, 'sso');

        if (!$normalized) {
            $request->session()->forget(['bio_profile', 'bio_lookup']);

            return;
        }

        $request->session()->put('bio_profile', $normalized);

        $request->session()->forget('bio_lookup');
    }

    /**
     * Logout by clearing the stored API session data.
     */
    public function logout(Request $request)
    {
        $request->session()->forget([
            'api_token',
            'api_client_username',
            'api_user',
            'login_payload',
            'last_api_response',
            'sso_payload',
            'bio_profile', // Also clear the profile data
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'You have been logged out.');
    }
}