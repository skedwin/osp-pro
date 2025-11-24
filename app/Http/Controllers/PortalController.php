<?php

namespace App\Http\Controllers;

use App\Support\NcKenyaProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    /**
     * Display the API dashboard if a token is present.
     */
    public function dashboard(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        return view('portal.dashboard', $this->portalViewData($request));
    }

    public function profile(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        return view('portal.profile', $this->portalViewData($request));
    }

    /**
     * Proxy an arbitrary request to the remote API using the stored token.
     */
    public function proxy(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');

        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
            'method' => ['required', 'in:GET,POST,PUT,PATCH,DELETE'],
            'payload' => ['nullable', 'string'],
        ]);

        $endpoint = ltrim($validated['endpoint'], '/');
        $method = Str::upper($validated['method']);

        $jsonPayload = null;
        if (!empty($validated['payload'])) {
            $jsonPayload = json_decode($validated['payload'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withInput()
                    ->withErrors(['payload' => 'Payload must be valid JSON.']);
            }
        }

        $baseUrl = rtrim(config('services.nckenya.portal_base_url', 'https://api.nckenya.go.ke'), '/');
        $client = Http::withToken($token)->acceptJson();
        $url = $baseUrl . '/' . $endpoint;

        $response = $method === 'GET'
            ? $client->get($url, $jsonPayload ?? [])
            : $client->send($method, $url, ['json' => $jsonPayload]);

        $request->session()->put('last_api_response', [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json() ?? $response->body(),
        ]);

        return redirect()
            ->route('portal.dashboard')
            ->with('status', "Executed {$method} {$endpoint}");
    }

    /**
     * Update profile information.
     */
    public function updateProfile(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please log in again.',
                    'redirect' => route('login'),
                ], 401);
            }
            return $redirect;
        }

        $token = $request->session()->get('api_token');

        $validated = $request->validate([
            'index_id' => ['nullable', 'string', 'max:50'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile_no' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:M,F'],
            'address' => ['nullable', 'string'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'birth_cert_no' => ['nullable', 'string', 'max:50'],
            'profile_pic' => ['nullable', 'string', 'max:255'],
        ]);

        // Get current profile from session
        $bioProfile = $request->session()->get('bio_profile', []);
        $profileSummary = data_get($bioProfile, 'profile', []);

        // Update profile data in session (for immediate display)
        if (!empty($validated)) {
            // Reconstruct full name from first and last name
            if (isset($validated['first_name']) || isset($validated['last_name'])) {
                $firstName = $validated['first_name'] ?? data_get($profileSummary, 'Name', '');
                $lastName = $validated['last_name'] ?? '';
                if ($lastName) {
                    $profileSummary['Name'] = trim($firstName . ' ' . $lastName);
                } else {
                    $profileSummary['Name'] = $firstName;
                }
            }

            // Map form fields to profile structure
            $fieldMapping = [
                'email' => 'Email',
                'mobile_no' => 'MobileNo',
                'date_of_birth' => 'DateOfBirth',
                'gender' => 'Gender',
                'address' => 'Address',
                'passport_number' => 'PassportNumber',
                'birth_cert_no' => 'BirthCertNo',
            ];

            foreach ($fieldMapping as $formField => $profileField) {
                if (isset($validated[$formField])) {
                    $profileSummary[$profileField] = $validated[$formField];
                }
            }

            // Update avatar if provided
            if (!empty($validated['profile_pic'] ?? null)) {
                $bioProfile['avatar'] = $validated['profile_pic'];
            }

            // Update bio_profile in session
            $bioProfile['profile'] = $profileSummary;
            $request->session()->put('bio_profile', $bioProfile);

            // Send update to remote bio endpoint
            $baseUrl = rtrim(config('services.nckenya.portal_base_url', 'https://api.nckenya.go.ke'), '/');
            $updateUrl = $baseUrl . '/bio/update';

            $indexId = $validated['index_id']
                ?? data_get($profileSummary, 'IndexNo')
                ?? data_get($profileSummary, 'index_id');

            if ($indexId) {
                $apiPayload = [
                    'index_id' => $indexId,
                    'address' => $validated['address'] ?? data_get($profileSummary, 'Address'),
                    'email' => $validated['email'] ?? data_get($profileSummary, 'Email'),
                    'mobileno' => $validated['mobile_no'] ?? data_get($profileSummary, 'MobileNo'),
                    'profile-pic' => $validated['profile_pic'] ?? data_get($bioProfile, 'avatar'),
                ];

                try {
                    $resp = Http::withToken($token)
                        ->acceptJson()
                        ->post($updateUrl, $apiPayload);

                    // Store last API response for debugging
                    $request->session()->put('last_api_response', [
                        'status' => $resp->status(),
                        'body' => $resp->json() ?? $resp->body(),
                    ]);

                    if ($resp->successful()) {
                        $body = $resp->json();
                        // Some endpoints wrap the actual updated profile under 'message'
                        $updated = is_array($body) && isset($body['message']) && is_array($body['message'])
                            ? $body['message']
                            : (is_array($body) ? $body : []);

                        // Map returned fields to local profile structure
                        $map = [
                            'address' => 'Address',
                            'email' => 'Email',
                            'mobileno' => 'MobileNo',
                            'profile-pic' => 'ProfilePic',
                            'profile_pic' => 'ProfilePic',
                            'ProfilePic' => 'ProfilePic',
                            'index_id' => 'IndexNo',
                            'indexId' => 'IndexNo',
                            'IndexNo' => 'IndexNo',
                        ];

                        foreach ($map as $k => $localKey) {
                            if (array_key_exists($k, $updated) && $updated[$k] !== null) {
                                // Normalize key names used elsewhere
                                if ($localKey === 'ProfilePic') {
                                    $bioProfile['avatar'] = $updated[$k];
                                }
                                $profileSummary[$localKey] = $updated[$k];
                            }
                        }

                        // As a final fallback, if the returned payload contains any common avatar keys, pick the first available
                        if (empty($bioProfile['avatar'])) {
                            foreach (['ProfilePic', 'profile-pic', 'profile_pic', 'profilePic'] as $tryKey) {
                                if (!empty($updated[$tryKey])) {
                                    $bioProfile['avatar'] = $updated[$tryKey];
                                    $profileSummary['ProfilePic'] = $updated[$tryKey];
                                    break;
                                }
                            }
                        }

                        // Persist merged profile to session
                        $bioProfile['profile'] = $profileSummary;
                        $request->session()->put('bio_profile', $bioProfile);
                    }
                } catch (\Throwable $th) {
                    // Log and preserve local session changes; surface error for AJAX clients
                    Log::error('Failed to update remote bio profile', [
                        'url' => $updateUrl,
                        'payload' => $apiPayload,
                        'error' => $th->getMessage(),
                    ]);
                    $request->session()->put('last_api_response', [
                        'status' => 0,
                        'body' => $th->getMessage(),
                    ]);
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Failed to update profile on remote service. Your changes are saved locally.',
                        ], 502);
                    }
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.'
            ]);
        }
        return redirect()
            ->route('portal.profile')
            ->with('status', 'Profile updated successfully.');
    }

    protected function guardSsoPortal(Request $request): ?RedirectResponse
    {
        $token = $request->session()->get('api_token');
        $ssoPayload = $request->session()->get('sso_payload');

        if (!$token || !$ssoPayload) {
            return redirect()
                ->route('login')
                ->with('warning', 'Please authenticate via the SSO endpoint to continue.');
        }

        $this->ensureProfilePrimed($request);

        return null;
    }

    protected function portalViewData(Request $request): array
    {
        $baseUrl = rtrim(config('services.nckenya.portal_base_url', 'https://api.nckenya.go.ke'), '/');

        return [
            'token' => $request->session()->get('api_token'),
            'clientUsername' => $request->session()->get('api_client_username'),
            'user' => $request->session()->get('api_user'),
            'loginPayload' => $request->session()->get('login_payload'),
            'ssoPayload' => $request->session()->get('sso_payload'),
            'lastResponse' => $request->session()->get('last_api_response'),
            'portalBaseUrl' => $baseUrl,
            'bioProfile' => $request->session()->get('bio_profile'),
        ];
    }

    protected function ensureProfilePrimed(Request $request): void
    {
        if ($request->session()->has('bio_profile')) {
            return;
        }

        $ssoPayload = $request->session()->get('sso_payload');

        if (!is_array($ssoPayload)) {
            return;
        }

        $normalized = NcKenyaProfile::fromPayload($ssoPayload, 'sso');

        if (!$normalized) {
            return;
        }

        $request->session()->put('bio_profile', $normalized);
        $request->session()->forget('bio_lookup');
    }
}

