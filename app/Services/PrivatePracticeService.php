<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrivatePracticeService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.nck_api.url', 'https://api.nckenya.go.ke'), '/');
    }

    /**
     * Submit Private Practice application
     */
    public function submit(array $payload, ?string $token = null)
    {
        $http = Http::acceptJson()->timeout(30);
        if ($token) {
            $http = $http->withToken($token);
        }

        $url = $this->baseUrl . '/private-practice/license/apply';
        
        Log::info('Submitting Private Practice application to API', [
            'url' => $url,
            'payload' => $payload,
            'has_token' => !empty($token),
        ]);
        
        try {
            $response = $http->post($url, $payload);
            
            Log::info('Private Practice API response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json(),
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Private Practice submit error: ' . $e->getMessage(), [
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
