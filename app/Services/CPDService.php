<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CPDService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.nck_api.url', 'https://api.nckenya.go.ke');
    }

    /**
     * Get CPD categories from API
     */
    public function getCPDCategories()
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->get($this->baseUrl . '/cpd/categories');

            if ($response->successful()) {
                $data = $response->json();
                return data_get($data, 'message', []);
            }

            Log::warning('CPD Categories API failed', ['status' => $response->status()]);
            return [];
            
        } catch (\Exception $e) {
            Log::error('CPD Categories Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CPD data for a user from the profile endpoint
     */
    public function getCPDHistory($indexId)
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->get($this->baseUrl . '/profile', [
                    'index_id' => $indexId
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return data_get($data, 'profile.cpd', []);
            }

            Log::warning('Profile API failed for CPD data', ['status' => $response->status()]);
            return [];
            
        } catch (\Exception $e) {
            Log::error('Profile API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CPD events for a user
     */
    public function getCPDEvents($indexId)
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->get($this->baseUrl . '/cpd/events', [
                    'index_id' => $indexId
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return data_get($data, 'message', []);
            }

            Log::warning('CPD Events API failed', ['status' => $response->status()]);
            return [];
            
        } catch (\Exception $e) {
            Log::error('CPD Events Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Submit self-reporting CPD activity
     */
    public function submitSelfReporting($data)
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post($this->baseUrl . '/cpd/self-reporting', $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message' => data_get($responseData, 'message', 'Activity submitted successfully'),
                    'data' => $responseData
                ];
            }

            $errorMessage = $response->body() ?? 'Unknown error occurred';
            Log::warning('CPD Self-reporting failed', [
                'status' => $response->status(),
                'error' => $errorMessage
            ]);

            return [
                'success' => false,
                'message' => 'Failed to submit activity: ' . $errorMessage
            ];
            
        } catch (\Exception $e) {
            Log::error('CPD Self-reporting Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Network error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Submit points for CPD activity
     */
    public function submitPoints($data)
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post($this->baseUrl . '/points', $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message' => data_get($responseData, 'message', 'Points recorded successfully'),
                    'data' => $responseData
                ];
            }

            $errorMessage = $response->body() ?? 'Unknown error occurred';
            Log::warning('CPD Points API failed', [
                'status' => $response->status(),
                'error' => $errorMessage
            ]);

            return [
                'success' => false,
                'message' => 'Failed to record points: ' . $errorMessage
            ];
            
        } catch (\Exception $e) {
            Log::error('CPD Points Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Network error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Claim CPD token
     */
    public function claimToken($indexId)
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post($this->baseUrl . '/cpd/token/claim', [
                    'index_id' => $indexId
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message' => data_get($responseData, 'message', 'Token claimed successfully'),
                    'data' => $responseData
                ];
            }

            $errorMessage = $response->body() ?? 'Unknown error occurred';
            Log::warning('CPD Token Claim failed', [
                'status' => $response->status(),
                'error' => $errorMessage
            ]);

            return [
                'success' => false,
                'message' => 'Failed to claim token: ' . $errorMessage
            ];
            
        } catch (\Exception $e) {
            Log::error('CPD Token Claim Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Network error: ' . $e->getMessage()
            ];
        }
    }
}