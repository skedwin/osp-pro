<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PractitionerController extends Controller
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.nck_api.url', 'https://api.nckenya.go.ke'), '/');
    }

    /**
     * Show the License Renewals page
     */
    public function renewals(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');

        $counties = $this->fetchReferenceData('/counties', $token);
        
        // Log county data structure for debugging
        if (!empty($counties) && config('app.debug')) {
            Log::info('Counties data structure', [
                'count' => count($counties),
                'first_county' => $counties[0] ?? null,
                'first_county_keys' => !empty($counties[0]) ? array_keys($counties[0]) : []
            ]);
        }
        
        // Fetch CPD data using the updated CPDService
        $cpdService = app(\App\Services\CPDService::class);
        $bioProfile = $request->session()->get('bio_profile', []);
        $indexId = data_get($bioProfile, 'profile.id');

        Log::info('Renewals CPD fetch debug', [
            'bioProfile' => $bioProfile,
            'indexId' => $indexId,
        ]);

        $cpdData = [];
        if ($indexId) {
            $cpdData = $cpdService->getCPDHistory($indexId);
        }

    // Prefer backend-provided CPD summary values when available. ProfileService
    // now normalizes the profile and exposes a canonical `cpd_summary` key
    // while keeping `cpd` iterable for view components. Try `cpd_summary`
    // first then fall back to `cpd` for compatibility.
    $cpdSummary = data_get($bioProfile, 'cpd_summary', data_get($bioProfile, 'cpd', []));
    $requiredCpd = floatval(data_get($cpdSummary, 'cpd_requirement', 20));

        if (is_array($cpdSummary) && array_key_exists('current_points', $cpdSummary)) {
            $cpdTotal = floatval($cpdSummary['current_points']);
        } elseif (is_array($cpdSummary) && isset($cpdSummary[0]) && is_array($cpdSummary[0]) && array_key_exists('current_points', $cpdSummary[0])) {
            $cpdTotal = floatval($cpdSummary[0]['current_points']);
        } else {
            // Fallback: sum points from CPD activity items returned by CPDService
            $cpdTotal = floatval(collect($cpdData)->sum(function ($item) {
                return floatval(data_get($item, 'current_points', data_get($item, 'points_earned', data_get($item, 'points', 0))));
            }));
        }

        $hasCpd = ($cpdTotal >= $requiredCpd);

        return view('practitioner.renewals', [
            'title' => 'License Renewals',
            'page' => 'practitioner-renewals',
            'counties' => $counties,
            'employers' => $this->fetchReferenceData('/employers', $token),
            'workstationTypes' => $this->fetchReferenceData('/workstation-types', $token),
            'cpdData' => $cpdData,
            'hasCpd' => $hasCpd,
            'cpdTotal' => $cpdTotal,
            'requiredCpd' => $requiredCpd,
        ]);
    }

    /**
     * Process license renewal application
     */
    public function processRenewal(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $request->validate([
            'county_id' => 'required|integer',
            'employer_id' => 'required|integer',
            'workstation_type_id' => 'required|integer',
            // Make workstation_name optional (nullable) to allow using only workstation_id
            'workstation_name' => 'nullable|string|max:255',
            'workstation_id' => 'nullable|integer'
        ]);

        $token = $request->session()->get('api_token');
        $bioProfile = $request->session()->get('bio_profile', []);
        
        // Try multiple possible locations for index_id/IndexNo
        // The bio_profile structure has profile.IndexNo as the correct field
        $indexId = data_get($bioProfile, 'profile.id');
        
        Log::info('Extracting index_id for renewal', [
            'IndexNo' => data_get($bioProfile, 'profile.IndexNo'),
            'index_id' => data_get($bioProfile, 'profile.id'),
            'id' => data_get($bioProfile, 'profile.id'),
            'final_indexId' => $indexId,
            'bio_profile_keys' => array_keys($bioProfile),
            'profile_keys' => isset($bioProfile['profile']) ? array_keys($bioProfile['profile']) : []
        ]);

        if (!$indexId) {
            Log::error('No index_id found in bio_profile', [
                'bio_profile_structure' => $bioProfile
            ]);
            return back()->with('error', 'Unable to determine your practitioner index number. Please contact support.');
        }

        $renewalData = [
            'index_id' => $indexId,
            'renewal_date' => now()->setTimezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
            'workstation_id' => $request->workstation_id ? (int)$request->workstation_id : null,
            'employer_id' => $request->employer_id ? (int)$request->employer_id : null,
            'county_id' => $request->county_id ? (int)$request->county_id : null,
            // If workstation_name is not provided, send null and allow API/server to derive it
            'workstation_name' => $request->workstation_name ?? null,
        ];

        // If workstation_name is missing but workstation_id is present, try to derive the name
        if (empty($renewalData['workstation_name']) && !empty($renewalData['workstation_id'])) {
            $derivedName = null;

            // First try using the county-scoped workstations (best chance)
            if (!empty($renewalData['county_id'])) {
                try {
                    $workstations = $this->fetchWorkstationsByCounty((int)$renewalData['county_id'], $token);
                    if (!empty($workstations) && is_array($workstations)) {
                        foreach ($workstations as $ws) {
                            $wsId = $ws['id'] ?? $ws['workstation_id'] ?? null;
                            if ($wsId !== null && (string)$wsId === (string)$renewalData['workstation_id']) {
                                $derivedName = $ws['workstation'] ?? $ws['name'] ?? null;
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed deriving workstation name from county list', ['error' => $e->getMessage()]);
                }
            }

            // Fallback: try global reference data
            if (empty($derivedName)) {
                try {
                    $allWorkstations = $this->fetchReferenceData('/workstations', $token);
                    if (!empty($allWorkstations) && is_array($allWorkstations)) {
                        foreach ($allWorkstations as $ws) {
                            $wsId = $ws['id'] ?? $ws['workstation_id'] ?? null;
                            if ($wsId !== null && (string)$wsId === (string)$renewalData['workstation_id']) {
                                $derivedName = $ws['workstation'] ?? $ws['name'] ?? null;
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed deriving workstation name from global list', ['error' => $e->getMessage()]);
                }
            }

            if (!empty($derivedName)) {
                Log::info('Derived workstation_name for renewal', ['workstation_id' => $renewalData['workstation_id'], 'workstation_name' => $derivedName]);
                $renewalData['workstation_name'] = $derivedName;
            } else {
                Log::info('Could not derive workstation_name for renewal', ['workstation_id' => $renewalData['workstation_id']]);
            }
        }

        try {
            $response = $this->postToApi('/license/apply', $renewalData, $token);

            if ($response->successful()) {
                $payload = $response->json();
                // Try to extract invoice details if present
                $invoicePayload = data_get($payload, 'message') ?: $payload;

                // Flash invoice payload to session so invoices view can use it
                $request->session()->flash('invoice_payload', $invoicePayload);

                $message = data_get($payload, 'message', 'License renewal application submitted successfully!');
                // Extract invoice ID for redirect
                $invoiceId = data_get($invoicePayload, 'invoice_number') ?? data_get($invoicePayload, 'billRefNumber');
                if ($invoiceId) {
                    return redirect()->route('practitioner.invoices.show', ['id' => $invoiceId])
                        ->with('success', is_string($message) ? $message : 'License renewal application submitted successfully!');
                }
                // Fallback: redirect to invoices table
                    return redirect()->route('practitioner.invoices')->with('success', is_string($message) ? $message : 'License renewal application submitted successfully!');
            }

            $errorMessage = data_get($response->json(), 'message', 'Failed to submit renewal application. Please try again.');
            Log::warning('Renewal API Error', ['status' => $response->status(), 'body' => $response->json()]);

            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Renewal Process Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'An unexpected error occurred while processing your renewal.');
        }
    }

    /**
     * Fetch workstations for a county (AJAX)
     */
    public function getWorkstations(Request $request)
    {
        // Log that the endpoint was hit
        Log::info('=== getWorkstations ENDPOINT HIT ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'all_input' => $request->all(),
            'query_params' => $request->query(),
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson()
        ]);
        
        if ($redirect = $this->guardSsoPortal($request)) {
            Log::warning('getWorkstations - Unauthorized', [
                'has_token' => $request->session()->has('api_token'),
                'has_sso' => $request->session()->has('sso_payload')
            ]);
            return $request->ajax() || $request->expectsJson()
                ? response()->json(['success' => false, 'error' => 'Unauthorized'], 401)
                : $redirect;
        }

        try {
            // Accept both string and integer county_id (API returns string IDs)
            $request->validate(['county_id' => 'required']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('getWorkstations - Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $countyIdRaw = $request->input('county_id');
        // Convert to integer, but handle string IDs from API
        $countyId = is_numeric($countyIdRaw) ? (int)$countyIdRaw : null;
        
        if (!$countyId || $countyId < 1) {
            Log::error('getWorkstations - Invalid county_id', [
                'county_id_raw' => $countyIdRaw,
                'county_id_parsed' => $countyId
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Invalid county ID provided'
            ], 422);
        }
        $token = $request->session()->get('api_token');
        
        Log::info('getWorkstations called', [
            'county_id_raw' => $countyIdRaw,
            'county_id_parsed' => $countyId,
            'county_id_type' => gettype($countyIdRaw),
            'has_token' => !empty($token),
            'request_all' => $request->all(),
            'query_params' => $request->query()
        ]);
        
        // Fetch workstations for the selected county
        $workstations = $this->fetchWorkstationsByCounty($countyId, $token);

        Log::info('getWorkstations response', [
            'county_id' => $countyId,
            'workstations_count' => count($workstations),
            'workstations_sample' => !empty($workstations) ? array_slice($workstations, 0, 2) : []
        ]);

        // Debug: Log the full response structure
        Log::debug('Full workstations response', [
            'workstations' => $workstations,
            'is_array' => is_array($workstations),
            'empty' => empty($workstations),
            'count' => count($workstations)
        ]);

        // Always return response, even if empty, with debug info
        $response = [
            'success' => true,
            'county_id' => $countyId,
            'workstations' => $workstations,
            'count' => count($workstations),
            'timestamp' => now()->toDateTimeString()
        ];

        // Add debug info in development
        if (config('app.debug')) {
            $response['debug'] = [
                'is_array' => is_array($workstations),
                'empty' => empty($workstations),
                'first_item' => !empty($workstations) ? $workstations[0] : null,
                'all_keys' => !empty($workstations) && isset($workstations[0]) ? array_keys($workstations[0]) : [],
                'api_url_called' => $this->baseUrl . '/workstations/county?county_id=' . $countyId,
                'has_token' => !empty($token)
            ];
        }

        Log::info('=== SENDING RESPONSE ===', [
            'county_id' => $countyId,
            'workstations_count' => count($workstations),
            'response' => $response
        ]);

        return response()->json($response);
    }

    /**
     * Fetch license applications for the logged-in practitioner (AJAX)
     */
    public function getApplications(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $request->ajax() || $request->expectsJson()
                ? response()->json(['success' => false, 'error' => 'Unauthorized'], 401)
                : $redirect;
        }

        $token = $request->session()->get('api_token');
        $bioProfile = $request->session()->get('bio_profile', []);

        // Derive index_id similarly to processRenewal
        $indexId = data_get($bioProfile, 'profile.id');
           
        if (!$indexId) {
            return response()->json(['success' => false, 'error' => 'Unable to determine your practitioner index number'], 422);
        }

        try {
            // Use fetchReferenceData to normalize the response structure
            $endpoint = '/license/applications?index_id=' . urlencode((string)$indexId);
            $applications = $this->fetchReferenceData($endpoint, $token);

            return response()->json([
                'success' => true,
                'index_id' => $indexId,
                'applications' => $applications
            ]);
        } catch (\Exception $e) {
            Log::error('getApplications error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'error' => 'Failed to fetch applications'], 500);
        }
    }

    /**
     * Render invoices page with list of applications/invoices
     */
    public function invoices(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');
        $bioProfile = $request->session()->get('bio_profile', []);

        $indexId = data_get($bioProfile, 'profile.id');

        $applications = [];
        try {
            if ($indexId) {
                $endpoint = '/license/applications?index_id=' . urlencode((string)$indexId);
                $applications = $this->fetchReferenceData($endpoint, $token);
            }
        } catch (\Exception $e) {
            Log::warning('invoices fetch failed: ' . $e->getMessage());
        }

        // Sort applications most-recent-first (using invoice_date then created_at)
        if (is_array($applications) && count($applications) > 0) {
            usort($applications, function($a, $b) {
                $invA = $a['invoice_details'] ?? $a;
                $invB = $b['invoice_details'] ?? $b;
                $dateA = strtotime($invA['invoice_date'] ?? $invA['created_at'] ?? '');
                $dateB = strtotime($invB['invoice_date'] ?? $invB['created_at'] ?? '');
                return $dateB <=> $dateA;
            });
        }

        // Server-side pagination (simple in-controller paginator)
        $perPage = 5;
        $page = (int) $request->query('page', 1);
        $invoicePayload = $request->session()->get('invoice_payload', null);

        if (is_array($applications)) {
            $total = count($applications);
            $offset = ($page - 1) * $perPage;
            $items = array_slice($applications, $offset, $perPage);
            $applications = new LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        return view('practitioner.renewals.invoices', [
            'title' => 'Invoices',
            'page' => 'practitioner-invoices',
            'applications' => $applications,
            'invoice_payload' => $invoicePayload
        ]);
    }

    /**
     * Show a single invoice/application details and provide pay section
     */
    public function invoiceDetails(Request $request, $id)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');
        $bioProfile = $request->session()->get('bio_profile', []);

        $indexId = data_get($bioProfile, 'profile.id');
        if (!$indexId) {
            return redirect()->route('practitioner.invoices')->with('error', 'Unable to determine your practitioner index number.');
        }

        $applications = [];
        try {
            $endpoint = '/license/applications?index_id=' . urlencode((string)$indexId);
            $applications = $this->fetchReferenceData($endpoint, $token);
        } catch (\Exception $e) {
            Log::warning('invoiceDetails fetch failed: ' . $e->getMessage());
        }

        // Try to find matching application by application_id or invoice number / billRefNumber
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
            return redirect()->route('practitioner.invoices')->with('error', 'Invoice not found.');
        }

        $invoice = $foundApp['invoice_details'] ?? $foundApp;

        return view('practitioner.renewals.invoice_details', [
            'title' => 'Invoice Details',
            'page' => 'practitioner-invoice-details',
            'application' => $foundApp,
            'invoice' => $invoice,
        ]);
    }

    /**
     * Pesaflow callback endpoint invoked by the payment iframe or provider after success.
     * This will return a tiny page that instructs the parent/top window to navigate to invoices.
     */
    public function pesaflowCallback(Request $request)
    {
        // Log payload for debugging
        Log::info('Pesaflow callback received', [
            'method' => $request->method(),
            'all' => $request->all(),
            'query' => $request->query()
        ]);

        // Optionally: You could perform server-side verification here, mark invoice as paid via API, etc.

        // Render a minimal page that will redirect the top-level window to the invoices page
        return view('practitioner.pesaflow_callback', [
            'message' => 'Payment received. Redirecting to invoices...',
            'redirect' => route('practitioner.invoices')
        ]);
    }

    /**
     * Centralized GET API call
     */
    private function fetchReferenceData(string $endpoint, ?string $token = null): array
    {
        try {
            $http = Http::acceptJson()->timeout(15);
            if ($token) $http = $http->withToken($token);

            $url = str_starts_with($endpoint, 'http') ? $endpoint : $this->baseUrl . $endpoint;
            $response = $http->get($url);

            if (!$response->successful()) {
                Log::warning('Reference Data API failed', ['endpoint' => $url, 'status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            $payload = $response->json();

            // Normalize nested structures
            if (isset($payload['message']) && is_array($payload['message'])) {
                // Check for nested arrays like {"message": {"workstations": [...]}}
                foreach ($payload['message'] as $key => $value) {
                    if (is_array($value) && !empty($value) && isset($value[0])) {
                        // This is an array of items (workstations, counties, employers, etc.)
                        return $value;
                    }
                }
                // If message is directly an array, return it
                return $payload['message'];
            }

            return is_array($payload) ? $payload : [];
        } catch (\Exception $e) {
            Log::error('Reference Data API Error', ['endpoint' => $endpoint, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return [];
        }
    }

    /**
     * Fetch workstations for a specific county
     */
    private function fetchWorkstationsByCounty(int $countyId, ?string $token = null): array
    {
        try {
            $http = Http::acceptJson()->timeout(15);
            if ($token) $http = $http->withToken($token);

            // Build URL with query parameter - ensure county_id is properly formatted
            $url = $this->baseUrl . '/workstations/county?county_id=' . urlencode((string)$countyId);
            
            Log::info('Fetching workstations from API', [
                'county_id' => $countyId,
                'county_id_type' => gettype($countyId),
                'url' => $url,
                'has_token' => !empty($token),
                'token_preview' => $token ? substr($token, 0, 20) . '...' : 'none'
            ]);
            
            $response = $http->get($url);
            
            // Log response details
            Log::info('API Response received', [
                'county_id' => $countyId,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 200)
            ]);

            if (!$response->successful()) {
                Log::warning('Workstations by County API failed', [
                    'county_id' => $countyId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                return [];
            }

            $payload = $response->json();
            
            // Log the raw response for debugging
            Log::info('Workstations API raw response', [
                'county_id' => $countyId,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'json_payload' => $payload
            ]);
            
            Log::info('Workstations API response structure', [
                'county_id' => $countyId,
                'payload_keys' => is_array($payload) ? array_keys($payload) : 'not_array',
                'has_message' => isset($payload['message']),
                'message_keys' => isset($payload['message']) && is_array($payload['message']) ? array_keys($payload['message']) : 'not_array',
                'has_workstations' => isset($payload['message']['workstations']),
                'workstations_count' => isset($payload['message']['workstations']) && is_array($payload['message']['workstations']) ? count($payload['message']['workstations']) : 0,
                'full_payload' => $payload
            ]);

            // Workstations API returns: {"status":"200","message":{"workstations":[...]}}
            if (isset($payload['message']['workstations']) && is_array($payload['message']['workstations'])) {
                $workstations = $payload['message']['workstations'];
                Log::info('Workstations extracted successfully', [
                    'county_id' => $countyId,
                    'count' => count($workstations)
                ]);
                return $workstations;
            }

            // Fallback: check if message is directly an array
            if (isset($payload['message']) && is_array($payload['message'])) {
                // Check if it's an array of workstations (not nested)
                if (!empty($payload['message']) && isset($payload['message'][0])) {
                    Log::info('Workstations found in message array', [
                        'county_id' => $countyId,
                        'count' => count($payload['message'])
                    ]);
                    return $payload['message'];
                }
            }

            // Fallback: check if payload is directly an array
            if (is_array($payload) && !empty($payload) && isset($payload[0])) {
                Log::info('Workstations found in payload array', [
                    'county_id' => $countyId,
                    'count' => count($payload)
                ]);
                return $payload;
            }

            Log::warning('Workstations by County API returned invalid format', [
                'county_id' => $countyId,
                'payload_structure' => is_array($payload) ? array_keys($payload) : gettype($payload),
                'payload_sample' => is_array($payload) ? json_encode(array_slice($payload, 0, 2)) : $payload
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Workstations by County API Error: ' . $e->getMessage(), [
                'county_id' => $countyId,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Centralized POST API call
     */
    private function postToApi(string $endpoint, array $data, ?string $token = null)
    {
        $http = Http::acceptJson()->timeout(15);
        if ($token) $http = $http->withToken($token);
        $url = str_starts_with($endpoint, 'http') ? $endpoint : $this->baseUrl . $endpoint;

        return $http->post($url, $data);
    }

    /**
     * Ensure SSO authentication
     */
    protected function guardSsoPortal(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $token = $request->session()->get('api_token');
        $ssoPayload = $request->session()->get('sso_payload');

        if (!$token || !$ssoPayload) {
            return redirect()->route('login')->with('warning', 'Please authenticate via the SSO endpoint to continue.');
        }

        return null;
    }

    /**
     * Show the Outmigration Application page
     */
    public function outmigration(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');

        // Fetch reference data needed for the form
        $countries = $this->fetchReferenceData('/countries', $token);
        $counties = $this->fetchReferenceData('/counties', $token);
        $employers = $this->fetchReferenceData('/employers', $token);
        $workstationTypes = $this->fetchReferenceData('/workstation-types', $token);

        // Marital status options (typically 1=Single, 2=Married, etc. - adjust based on API)
        $maritalStatusOptions = [
            ['id' => '1', 'name' => 'Single'],
            ['id' => '2', 'name' => 'Married'],
            ['id' => '3', 'name' => 'Divorced'],
            ['id' => '4', 'name' => 'Widowed'],
        ];

        // Employment status options
        $employmentStatusOptions = [
            ['id' => '1', 'name' => 'Employed'],
            ['id' => '2', 'name' => 'Self-Employed'],
            ['id' => '3', 'name' => 'Unemployed'],
            ['id' => '4', 'name' => 'Retired'],
        ];

        // Planning return options (1=Yes, 2=No)
        $planningReturnOptions = [
            ['id' => '1', 'name' => 'Yes'],
            ['id' => '2', 'name' => 'No'],
        ];

        // Outmigration reason options
        $outmigrationReasonOptions = [
            ['id' => '1', 'name' => 'Better Employment Opportunities'],
            ['id' => '2', 'name' => 'Higher Education'],
            ['id' => '3', 'name' => 'Family Reasons'],
            ['id' => '4', 'name' => 'Personal Development'],
            ['id' => '5', 'name' => 'Other'],
        ];

        return view('practitioner.outmigration', [
            'title' => 'Outmigration Application',
            'page' => 'practitioner-outmigration',
            'countries' => $countries,
            'counties' => $counties,
            'employers' => $employers,
            'workstationTypes' => $workstationTypes,
            'maritalStatusOptions' => $maritalStatusOptions,
            'employmentStatusOptions' => $employmentStatusOptions,
            'planningReturnOptions' => $planningReturnOptions,
            'outmigrationReasonOptions' => $outmigrationReasonOptions,
        ]);
    }

    // Simple view methods
    public function privatePractice() { return view('practitioner.private-practice', ['title' => 'Private Practice', 'page' => 'practitioner-private-practice']); }
    public function cpd() { return view('practitioner.cpd', ['title' => 'Continuing Professional Development', 'page' => 'practitioner-cpd']); }
}
