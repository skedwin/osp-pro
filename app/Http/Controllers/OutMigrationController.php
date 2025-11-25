<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutMigrationRequest;
use App\Services\OutMigrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OutMigrationController extends Controller
{
    protected OutMigrationService $service;
    protected string $baseUrl;
    protected string $outmigrationBaseUrl;

    public function __construct(OutMigrationService $service)
    {
        $this->service = $service;
        $this->baseUrl = rtrim(config('services.nck_api.url', 'https://api.nckenya.go.ke'), '/');
        $configured = config('services.outmigration_api.url', $this->baseUrl);
        $this->outmigrationBaseUrl = rtrim($configured, '/');
    }

    public function apply(StoreOutMigrationRequest $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        $token = $request->session()->get('api_token');

        $bioProfile = $request->session()->get('bio_profile', []);
        $indexId = data_get($bioProfile, 'profile.id');
        if (!$indexId) {
            Log::error('No index_id found in bio_profile when submitting outmigration', ['bio_profile' => $bioProfile]);
            return back()->with('error', 'Unable to determine your practitioner index number. Please contact support.');
        }

        $data = $request->validated();

        $payload = [
            'index_id' => (string)$indexId,
            'country_id' => (string)($data['country_id'] ?? ''),
            'application_date' => $data['application_date'] ?? now()->setTimezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
            'marital_status' => isset($data['marital_status']) ? (string)$data['marital_status'] : null,
            'dependants' => isset($data['dependants']) ? (string)$data['dependants'] : null,
            'employment_status' => isset($data['employment_status']) ? (string)$data['employment_status'] : null,
            'current_employer' => isset($data['current_employer']) ? (string)$data['current_employer'] : null,
            'workstation_type' => isset($data['workstation_type']) ? (string)$data['workstation_type'] : null,
            'workstation_id' => isset($data['workstation_id']) ? (string)$data['workstation_id'] : null,
            'workstation_name' => $data['workstation_name'] ?? null,
            'department' => $data['department'] ?? null,
            'current_position' => $data['current_position'] ?? null,
            'experience_years' => isset($data['experience_years']) ? (string)$data['experience_years'] : null,
            'duration_current_employer' => isset($data['duration_current_employer']) ? (string)$data['duration_current_employer'] : null,
            'planning_return' => isset($data['planning_return']) ? (string)$data['planning_return'] : null,
            'outmigration_reason' => isset($data['outmigration_reason']) ? (string)$data['outmigration_reason'] : null,
            'verification_cadres' => $data['verification_cadres'] ?? null,
        ];

        $payloadHash = md5(json_encode($payload));
        $recentSubmission = $request->session()->get('outmigration.last_submission');

        if ($recentSubmission && data_get($recentSubmission, 'hash') === $payloadHash) {
            $secondsSinceLast = time() - (int) data_get($recentSubmission, 'timestamp', 0);

            if ($secondsSinceLast >= 0 && $secondsSinceLast <= 90) {
                Log::info('Duplicate outmigration submission prevented', [
                    'index_id' => $indexId,
                    'seconds_since_last' => $secondsSinceLast,
                ]);

                $message = data_get(
                    $recentSubmission,
                    'message',
                    'We already received this outmigration application recently. Please wait a moment before submitting again.'
                );

                return back()->with('info', $message);
            }
        }

        // Handle file upload and produce public URL if provided
        if ($request->hasFile('form_attached')) {
            try {
                $path = $request->file('form_attached')->store('outmigration', 'public');
                $payload['form_attached'] = Storage::url($path);
            } catch (\Exception $e) {
                Log::warning('Failed storing outmigration form attachment', ['error' => $e->getMessage()]);
            }
        }

        try {
            $response = $this->service->submit($payload, $token);

            if ($response->successful()) {
                $resp = $response->json();
                $message = data_get($resp, 'message', 'Outmigration application submitted successfully!');
                $invoicePayload = is_array($message) ? $message : $resp;

                $request->session()->flash('outmigration_invoice_payload', $invoicePayload);
                $request->session()->put('outmigration.last_submission', [
                    'hash' => $payloadHash,
                    'timestamp' => time(),
                    'message' => is_string($message) ? $message : 'Outmigration application submitted successfully!',
                ]);

                // After successful application, redirect to invoices list
                return redirect()->route('practitioner.outmigration.invoices')
                    ->with('success', is_string($message) ? $message : 'Outmigration application submitted successfully!');
            }

            $errorMessage = data_get($response->json(), 'message', 'Failed to submit outmigration application. Please try again.');
            Log::warning('Outmigration API Error', ['status' => $response->status(), 'body' => $response->json()]);

            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Outmigration Process Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'An unexpected error occurred while processing your outmigration application.');
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
            return redirect()->route('practitioner.outmigration')
                ->with('error', 'Unable to determine your practitioner index number.');
        }

        $applications = [];

        try {
            $applications = $this->fetchOutmigrationApplications($indexId, $token);
        } catch (\Exception $e) {
            Log::warning('Outmigration invoices fetch failed', ['error' => $e->getMessage()]);
        }

        if (is_array($applications) && count($applications) > 0) {
            usort($applications, function ($a, $b) {
                $invA = $a['invoice_details'] ?? $a;
                $invB = $b['invoice_details'] ?? $b;
                $dateA = strtotime($invA['invoice_date'] ?? $invA['created_at'] ?? '');
                $dateB = strtotime($invB['invoice_date'] ?? $invB['created_at'] ?? '');
                return $dateB <=> $dateA;
            });
        }

        $perPage = 5;
        $page = (int) $request->query('page', 1);
        $invoicePayload = $request->session()->get('outmigration_invoice_payload', null);

        if (is_array($applications)) {
            $total = count($applications);
            $offset = ($page - 1) * $perPage;
            $items = array_slice($applications, $offset, $perPage);
            $applications = new LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        return view('practitioner.outmigration.invoices', [
            'title' => 'Outmigration Invoices',
            'page' => 'practitioner-outmigration-invoices',
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
            return redirect()->route('practitioner.outmigration.invoices')
                ->with('error', 'Unable to determine your practitioner index number.');
        }

        $applications = [];
        try {
            $applications = $this->fetchOutmigrationApplications($indexId, $token);
        } catch (\Exception $e) {
            Log::warning('Outmigration invoice details fetch failed', ['error' => $e->getMessage()]);
        }

        Log::info('OutMigration invoiceDetails lookup', [
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
            return redirect()->route('practitioner.outmigration.invoices')
                ->with('error', 'Invoice not found.');
        }

        $invoice = $foundApp['invoice_details'] ?? $foundApp;

        return view('practitioner.outmigration.invoice_details', [
            'title' => 'Outmigration Invoice Details',
            'page' => 'practitioner-outmigration-invoice-details',
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

    private function fetchOutmigrationApplications(string $indexId, ?string $token = null): array
    {
        try {
            $http = Http::acceptJson()->timeout(20);
            if ($token) {
                $http = $http->withToken($token);
            }

            $url = $this->outmigrationBaseUrl . '/outmigration/applications?index_id=' . urlencode((string)$indexId);
            $response = $http->get($url);

            if (!$response->successful()) {
                Log::warning('Outmigration applications API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);
                return [];
            }

            $payload = $response->json();

            if (isset($payload['message']) && is_array($payload['message'])) {
                // Check for outmigration_applications key
                if (isset($payload['message']['outmigration_applications']) && is_array($payload['message']['outmigration_applications'])) {
                    return $payload['message']['outmigration_applications'];
                }
                
                if (isset($payload['message']['applications']) && is_array($payload['message']['applications'])) {
                    return $payload['message']['applications'];
                }

                if (!empty($payload['message']) && isset($payload['message'][0])) {
                    return $payload['message'];
                }

                return $payload['message'];
            }

            return is_array($payload) ? $payload : [];
        } catch (\Exception $e) {
            Log::error('Outmigration applications fetch error: ' . $e->getMessage(), [
                'index_id' => $indexId,
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }
}
