<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutMigrationService
{
    protected string $baseUrl;

    public function __construct()
    {
        $configured = config('services.outmigration_api.url')
            ?? config('services.nck_api.url', 'https://api.nckenya.go.ke');
        $this->baseUrl = rtrim($configured, '/');
    }

    /**
     * Submit outmigration application to API
     */
    public function submit(array $data, ?string $token = null)
    {
        try {
            $http = Http::acceptJson()->timeout(30);
            if ($token) {
                $http = $http->withToken($token);
            }

            $url = $this->baseUrl . '/outmigration/apply';
            
            Log::info('Submitting outmigration application', [
                'url' => $url,
                'index_id' => $data['index_id'] ?? null,
                'has_token' => !empty($token)
            ]);

            $response = $http->post($url, $data);

            if (!$response->successful()) {
                Log::warning('Outmigration API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Outmigration Service Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
    }
}

