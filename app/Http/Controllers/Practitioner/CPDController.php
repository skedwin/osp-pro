<?php

namespace App\Http\Controllers\Practitioner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CPDController extends Controller
{
    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.nck_api.url', 'https://api.nckenya.go.ke');
    }

    /* ============================================================
     *  CENTRALIZED API CLIENT
     * ============================================================ */
    private function api(Request $request = null)
    {
        $token = $request?->session()->get('api_token');

        $client = Http::timeout(15)->acceptJson();

        return $token ? $client->withToken($token) : $client;
    }

    /* ============================================================
     *  GENERIC API FETCH HELPER
     * ============================================================ */
    private function fetch(string $endpoint, array $params = [], Request $request = null)
    {
        try {
            $response = $this->api($request)->get($this->apiBaseUrl . $endpoint, $params);

            if ($response->successful()) {
                return data_get($response->json(), 'message', []);
            }

            Log::warning("API fetch failed", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error("API fetch exception: $endpoint", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /* ============================================================
     *  GENERIC API POST HELPER
     * ============================================================ */
    private function post(string $endpoint, array $payload, Request $request = null): array
    {
        try {
            $response = $this->api($request)->post($this->apiBaseUrl . $endpoint, $payload);

            return [
                'success' => $response->successful(),
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error("API post exception: $endpoint", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => ['message' => 'API request failed.']
            ];
        }
    }

    /* ============================================================
     *  SSO GUARD
     * ============================================================ */
    protected function guardSsoPortal(Request $request): ?RedirectResponse
    {
        if (!$request->session()->has('api_token') || !$request->session()->has('sso_payload')) {
            return redirect()->route('login')->with('warning', 'Please authenticate via the SSO endpoint to continue.');
        }
        return null;
    }

    /* ============================================================
     *  CPD DASHBOARD
     * ============================================================ */
    public function index(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        try {
            $bio = $request->session()->get('bio_profile', []);
            $indexId = data_get($bio, 'profile.id');

            if (!$indexId) {
                return redirect()->route('portal.dashboard')->with('error', 'Unable to retrieve your index number.');
            }

            $cpdHistory    = $this->fetch('/cpd/history', ['index_id' => $indexId], $request);
            $cpdEvents     = $this->fetch('/cpd/events', ['index_id' => $indexId], $request);
            $cpdCategories = $this->fetch('/cpd/categories', [], $request);

            $cpdStats = $this->calculateCPDStats($cpdHistory, $cpdEvents);
             // Add profile summary data for CPD display
                $profileSummary = [
                         'cpd' => 'Points' // Display Profile CPD Pointa
                                 ];
            return view('practitioner.cpd.index', [
                'title'          => 'Continuing Professional Development',
                'page'           => 'practitioner-cpd',
                'cpdHistory'     => $cpdHistory,
                'cpdEvents'      => $cpdEvents,
                'cpdCategories'  => $cpdCategories,
                'cpdStats'       => $cpdStats,
                'currentYear'    => date('Y'),
                'bioProfile'     => $bio,
                'indexId'        => $indexId,
                'profileSummary' => $profileSummary // Add this line
            ]);

        } catch (\Exception $e) {
            Log::error('CPD index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('portal.dashboard')
                ->with('error', 'Unable to load CPD data.');
        }
    }

    /* ============================================================
     *  CREATE CPD ENTRY PAGE
     * ============================================================ */
    public function create(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        try {
            $categories = $this->fetch('/cpd/categories', [], $request);

            return view('practitioner.cpd.create', [
                'title' => 'Add CPD Activity',
                'page'  => 'practitioner-cpd',
                'cpdCategories' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('CPD create failed', ['error' => $e->getMessage()]);
            return redirect()->route('practitioner.cpd')
                ->with('error', 'Unable to load CPD categories.');
        }
    }

    /* ============================================================
     *  STORE SELF-REPORTED ACTIVITY
     * ============================================================ */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'          => 'required|string',
            'event_date'           => 'required|date',
            'event_title'          => 'required|string|max:255',
            'event_location'       => 'required|string|max:255',
            'cpd_evidence'         => 'required|string|max:500',
            'points'               => 'required|numeric|min:0.5|max:50',
            'duration'             => 'required|string|max:100',
            'activity_description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($redirect = $this->guardSsoPortal($request)) {
            return $redirect;
        }

        try {
            $bio = $request->session()->get('bio_profile', []);
            $indexId = data_get($bio, 'profile.id');

            if (!$indexId) {
                return back()->with('error', 'Index number missing.')->withInput();
            }

            // STEP 1: Self-reporting
            $selfReport = $this->post('/cpd/self-reporting', [
                'index_id'      => $indexId,
                'category_id'   => $request->category_id,
                'event_date'    => $request->event_date . ' 00:00:00',
                'event_title'   => $request->event_title,
                'event_location'=> $request->event_location,
                'cpd_evidence'  => $request->cpd_evidence,
            ], $request);

            if (!$selfReport['success']) {
                return back()->with('error', 'Self-reporting failed.')->withInput();
            }

            // STEP 2: Submit points
            $points = $this->post('/cpd/points', [
                'provider'           => 'Self-Reported',
                'index_id'           => $indexId,
                'member_name'        => data_get($bio, 'profile.Name'),
                'activity_reference' => uniqid(),
                'activity_title'     => $request->event_title,
                'activity_description'=> $request->activity_description,
                'duration'           => $request->duration,
                'date_started'       => $request->event_date,
                'date_completed'     => $request->event_date,
                'points'             => (string) $request->points,
            ], $request);

            if (!$points['success']) {
                return back()->with('error', 'Points submission failed.')->withInput();
            }

            return redirect()->route('practitioner.cpd')
                ->with('success', 'CPD activity reported successfully!');

        } catch (\Exception $e) {
            Log::error('CPD store failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred.')->withInput();
        }
    }

    /* ============================================================
     *  CLAIM TOKEN
     * ============================================================ */
    public function claimToken(Request $request)
    {
        if ($redirect = $this->guardSsoPortal($request)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Unauthorized'], 401)
                : $redirect;
        }

        try {
            $bio = $request->session()->get('bio_profile');
            $indexId = data_get($bio, 'profile.id');

            if (!$indexId) {
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => 'Index number missing'], 400)
                    : back()->with('error', 'Index number missing');
            }

            $request->validate([
                'event_token' => 'required|string'
            ]);

            $response = $this->post('/cpd/token/claim', [
                'index_id' => $indexId,
                'event_token' => $request->event_token
            ], $request);

            if ($request->expectsJson()) {
                return response()->json($response);
            }

            return back()->with(
                $response['success'] ? 'success' : 'error',
                data_get($response['data'], 'message', 'Token processed.')
            );

        } catch (\Exception $e) {
            Log::error('Token claim failed', ['error' => $e->getMessage()]);
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Server error'], 500)
                : back()->with('error', 'An error occurred.');
        }
    }

    /* ============================================================
     *  CPD STATISTICS
     * ============================================================ */
    private function calculateCPDStats(array $history, array $events): array
    {
        $required = 20;
        $year = date('Y');
        $total = 0;
        $count = 0;

        foreach (array_merge($history, $events) as $item) {
            $date = data_get($item, 'date_completed') 
                ?? data_get($item, 'event_date');

            if (!$date) continue;

            if (date('Y', strtotime($date)) == $year) {
                $points = (float) data_get($item, 'points', 
                           data_get($item, 'points_awarded', 0));

                $total += $points;
                $count++;
            }
        }

        $progress = min(($total / $required) * 100, 100);
        $remaining = max($required - $total, 0);

        $status = 'Needs Focus';
        $color = 'red';

        if ($total >= $required) {
            $status = 'Completed';
            $color = 'green';
        } elseif ($total >= 0.7 * $required) {
            $status = 'On Track';
            $color = 'blue';
        } elseif ($total >= 0.4 * $required) {
            $status = 'In Progress';
            $color = 'amber';
        }

        return [
            'total_points' => $total,
            'required_points' => $required,
            'progress_percentage' => $progress,
            'points_remaining' => $remaining,
            'activities_this_year' => $count,
            'status' => $status,
            'status_color' => $color
        ];
    }
}
