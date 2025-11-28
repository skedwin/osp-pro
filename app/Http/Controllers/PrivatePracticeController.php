<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\PrivatePracticeService;

class PrivatePracticeController extends Controller
{
    protected PrivatePracticeService $service;
    protected string $baseUrl;
    protected string $ppBaseUrl;

    public function __construct(PrivatePracticeService $service)
    {
        $this->service = $service;
        $this->baseUrl = rtrim(config('services.nck_api.url', 'https://api.nckenya.go.ke'), '/');
        $configured = config('services.private_practice_api.url', $this->baseUrl);
        $this->ppBaseUrl = rtrim($configured, '/');
    }

    public function apply(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');
        $bioProfile = $request->session()->get('bio_profile', []);
        $indexId = data_get($bioProfile, 'profile.id');
        
        if (!$indexId) {
            Log::error('No index_id found in bio_profile when submitting private practice', [
                'bio_profile' => $bioProfile,
                'session_keys' => array_keys($request->session()->all()),
            ]);
            return back()->with('error', 'Unable to determine your practitioner index number. Please log out and log back in.');
        }
        
        // Validate required fields
        $request->validate([
            'renewal_date' => 'nullable|date',
            'proposed_practice_id' => 'required',
            'practice_mode_id' => 'required',
            'county_id' => 'required',
            'workstation_id' => 'nullable',
            'town' => 'nullable|string|max:255',
        ], [
            'proposed_practice_id.required' => 'Please select a proposed practice type.',
            'practice_mode_id.required' => 'Please select a practice mode.',
            'county_id.required' => 'Please select a county.',
        ]);

        $data = $request->all();
        
        // Build payload matching the API's expected field names
        $payload = [
            'index_id' => (string)$indexId,
            'renewal_date' => $data['renewal_date'] ?? now()->setTimezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
            'proposed_practice_id' => (string)($data['proposed_practice_id'] ?? ''),
            'practice_mode_id' => (string)($data['practice_mode_id'] ?? ''),
            'county_id' => (string)($data['county_id'] ?? ''),
            'town' => $data['town'] ?? '',
            'workstation_id' => (string)($data['workstation_id'] ?? ''),
            'workstation_name' => $data['workstation_name'] ?? '',
        ];
        
        Log::info('Private Practice application submission', [
            'index_id' => $indexId,
            'payload' => $payload,
            'raw_request' => $data,
            'has_token' => !empty($token),
        ]);

        // Duplicate submission prevention (90-second window)
        $payloadHash = md5(json_encode($payload));
        $recentSubmission = $request->session()->get('private_practice.last_submission');

        if ($recentSubmission && data_get($recentSubmission, 'hash') === $payloadHash) {
            $secondsSinceLast = time() - (int) data_get($recentSubmission, 'timestamp', 0);

            if ($secondsSinceLast >= 0 && $secondsSinceLast <= 90) {
                Log::info('Duplicate private practice submission prevented', [
                    'index_id' => $indexId,
                    'seconds_since_last' => $secondsSinceLast,
                ]);

                $message = data_get(
                    $recentSubmission,
                    'message',
                    'We already received this private practice application recently. Please wait a moment before submitting again.'
                );

                return back()->with('info', $message);
            }
        }

        try {
            $response = $this->service->submit($payload, $token);
            if ($response->successful()) {
                $resp = $response->json();
                $message = data_get($resp, 'message', 'Private Practice application submitted successfully!');
                
                // Store invoice payload and submission tracking
                $invoicePayload = is_array($message) ? $message : $resp;
                $request->session()->flash('private_practice_invoice_payload', $invoicePayload);
                $request->session()->put('private_practice.last_submission', [
                    'hash' => $payloadHash,
                    'timestamp' => time(),
                    'message' => is_string($message) ? $message : 'Private Practice application submitted successfully!',
                ]);

                return redirect()->route('practitioner.private-practice.invoices')
                    ->with('success', is_string($message) ? $message : 'Private Practice application submitted successfully!');
            }
            $errorMessage = data_get($response->json(), 'message', 'Failed to submit Private Practice application.');
            Log::warning('Private Practice API Error', ['status' => $response->status(), 'body' => $response->json()]);
            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Private Practice apply error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Unexpected error while submitting your application.');
        }
    }

    public function invoices(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');
        $bioProfile = $request->session()->get('bio_profile', []);
        $indexId = data_get($bioProfile, 'profile.id');
        if (!$indexId) {
            return redirect()->route('practitioner.private-practice')
                ->with('error', 'Unable to determine your practitioner index number.');
        }

        $applications = [];
        try {
            $applications = $this->fetchPrivatePracticeApplications($indexId, $token);
        } catch (\Exception $e) {
            Log::warning('Private Practice invoices fetch failed', ['error' => $e->getMessage()]);
        }

        if (is_array($applications) && count($applications) > 0) {
            usort($applications, function ($a, $b) {
                $invA = $a['invoice_details'] ?? $a;
                $invB = $b['invoice_details'] ?? $b;
                $dateA = $invA['invoice_date'] ?? $a['renewal_date'] ?? $invA['created_at'] ?? '';
                $dateB = $invB['invoice_date'] ?? $b['renewal_date'] ?? $invB['created_at'] ?? '';
                $tA = $dateA ? strtotime($dateA) : 0;
                $tB = $dateB ? strtotime($dateB) : 0;
                return $tB <=> $tA;
            });
        }

        $perPage = 5;
        $page = (int) $request->query('page', 1);
        $invoicePayload = $request->session()->get('private_practice_invoice_payload');

        if (is_array($applications)) {
            $total = count($applications);
            $offset = ($page - 1) * $perPage;
            $items = array_slice($applications, $offset, $perPage);
            $applications = new LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        return view('practitioner.private_practice.invoices', [
            'title' => 'Private Practice Invoices',
            'page' => 'practitioner-private-practice-invoices',
            'applications' => $applications,
            'invoice_payload' => $invoicePayload,
        ]);
    }

    public function invoiceDetails(Request $request, $id)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');
        $bioProfile = $request->session()->get('bio_profile', []);
        $indexId = data_get($bioProfile, 'profile.id');
        
        if (!$indexId) {
            return redirect()->route('practitioner.private-practice.invoices')
                ->with('error', 'Unable to determine your practitioner index number.');
        }

        $applications = [];
        try {
            $applications = $this->fetchPrivatePracticeApplications($indexId, $token);
        } catch (\Exception $e) {
            Log::warning('Private Practice invoice details fetch failed', ['error' => $e->getMessage()]);
        }

        Log::info('Private Practice invoiceDetails lookup', [
            'id' => $id,
            'applications_count' => count($applications),
            'sample_app' => $applications[0] ?? null
        ]);

        $foundApp = null;
        foreach ($applications as $app) {
            $appId = $app['application_id'] ?? $app['applicationID'] ?? null;
            $inv = $app['invoice_details'] ?? $app;
            $bill = $inv['billRefNumber'] ?? $inv['invoice_number'] ?? null;

            if ($appId && (string)$appId === (string)$id) {
                $foundApp = $app;
                break;
            }

            if ($bill && (string)$bill === (string)$id) {
                $foundApp = $app;
                break;
            }
        }

        if (!$foundApp) {
            return redirect()->route('practitioner.private-practice.invoices')
                ->with('error', 'Invoice not found.');
        }

        $invoice = $foundApp['invoice_details'] ?? $foundApp;

        return view('practitioner.private_practice.invoice_details', [
            'title' => 'Private Practice Invoice Details',
            'page' => 'practitioner-private-practice-invoice-details',
            'application' => $foundApp,
            'invoice' => $invoice,
        ]);
    }

    protected function guardSsoPortal(Request $request): ?RedirectResponse
    {
        $token = $request->session()->get('api_token');
        $ssoPayload = $request->session()->get('sso_payload');
        if (!$token || !$ssoPayload) {
            return redirect()->route('login')->with('warning', 'Please authenticate via the SSO endpoint to continue.');
        }
        return null;
    }

    private function fetchPrivatePracticeApplications(string $indexId, ?string $token = null): array
    {
        try {
            $http = Http::acceptJson()->timeout(20);
            if ($token) {
                $http = $http->withToken($token);
            }
            $url = $this->ppBaseUrl . '/private-practice/license/applications?index_id=' . urlencode((string)$indexId);
            
            Log::info('Fetching Private Practice applications', [
                'url' => $url,
                'index_id' => $indexId,
            ]);
            
            $response = $http->get($url);
            
            if (!$response->successful()) {
                Log::warning('Private Practice applications API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);
                return [];
            }
            
            $payload = $response->json();
            
            Log::info('Private Practice applications response', [
                'payload_keys' => is_array($payload) ? array_keys($payload) : 'not_array',
                'has_message' => isset($payload['message']),
                'message_keys' => isset($payload['message']) && is_array($payload['message']) ? array_keys($payload['message']) : 'not_array',
            ]);
            
            // API returns: {"status":"200","message":{"license_applications":[...]}}
            if (isset($payload['message']['license_applications']) && is_array($payload['message']['license_applications'])) {
                return $payload['message']['license_applications'];
            }
            
            // Fallback: check if message is directly an array
            if (isset($payload['message']) && is_array($payload['message'])) {
                return $payload['message'];
            }
            
            return is_array($payload) ? $payload : [];
        } catch (\Exception $e) {
            Log::error('Private Practice applications fetch error: ' . $e->getMessage(), [
                'index_id' => $indexId,
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }
}
