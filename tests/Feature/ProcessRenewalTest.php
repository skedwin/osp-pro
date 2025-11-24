<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ProcessRenewalTest extends TestCase
{
    /**
     * Ensure processRenewal validates and posts to the external API with expected payload
     */
    public function test_process_renewal_posts_to_api_and_redirects()
    {
        // Fake all outgoing HTTP requests and return a successful JSON response
        Http::fake([
            '*' => Http::response(['message' => 'ok'], 200)
        ]);

        // Prepare session values required by guardSsoPortal and controller
        $bioProfile = ['profile' => ['IndexNo' => 'TEST123']];

        $response = $this->withSession([
            'api_token' => 'fake-token',
            'sso_payload' => ['user' => 'tester'],
            'bio_profile' => $bioProfile
        ])->post(route('practitioner.renewals.process'), [
            'county_id' => 1,
            'employer_id' => 2,
            'workstation_type_id' => 3,
            'workstation_id' => 5,
            // do not provide workstation_name to assert nullable handling
        ]);

    // Controller should redirect to invoices route with success message
    $response->assertRedirect(route('practitioner.invoices'));
        $response->assertSessionHas('success');

        // Ensure an HTTP request was sent to the external API
        Http::assertSent(function ($request) {
            // Expect the endpoint to be the license apply URL (path contains /license/apply)
            if (strpos($request->url(), '/license/apply') === false) {
                return false;
            }

            $body = $request->data();

            return isset($body['index_id']) && $body['index_id'] === 'TEST123'
                && isset($body['county_id']) && (int)$body['county_id'] === 1
                && array_key_exists('workstation_name', $body); // may be null
        });
    }

    /**
     * Ensure the controller derives workstation_name when only workstation_id is provided
     */
    public function test_process_renewal_derives_workstation_name()
    {
        // Fake responses for workstation lookup and license apply
        Http::fake(function ($request) {
            if (strpos($request->url(), '/workstations/county') !== false) {
                return Http::response([
                    'status' => '200',
                    'message' => [
                        'workstations' => [
                            ['id' => 5, 'workstation' => 'Derived Workstation']
                        ]
                    ]
                ], 200);
            }

            if (strpos($request->url(), '/license/apply') !== false) {
                return Http::response(['message' => 'ok'], 200);
            }

            return Http::response(null, 404);
        });

        $bioProfile = ['profile' => ['IndexNo' => 'IDX999']];

        $response = $this->withSession([
            'api_token' => 'fake-token',
            'sso_payload' => ['user' => 'tester'],
            'bio_profile' => $bioProfile
        ])->post(route('practitioner.renewals.process'), [
            'county_id' => 1,
            'employer_id' => 2,
            'workstation_type_id' => 3,
            'workstation_id' => 5,
            // workstation_name omitted intentionally
        ]);

    $response->assertRedirect(route('practitioner.invoices'));
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            if (strpos($request->url(), '/license/apply') === false) return false;

            $body = $request->data();
            return isset($body['workstation_name']) && $body['workstation_name'] === 'Derived Workstation';
        });
    }
}
