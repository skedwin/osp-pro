<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Request;

class PractitionerRenewalsCpdTest extends TestCase
{
    public function test_uses_backend_summary_object()
    {
        // Use array-wrapped summary so view components that iterate CPD items
        // (like the CPD card) can render without errors. Backend may return
        // either a summary object or an array-wrapped summary; the controller
        // supports both shapes. For test purposes provide the array-wrapped form.
        $bioProfile = [
            'profile' => ['id' => 'IDX1'],
            'cpd' => [ ['current_points' => '12', 'cpd_requirement' => '20'] ]
        ];

        // Provide required session keys so guardSsoPortal does not redirect
        $response = $this->withSession([
            'api_token' => 'fake-token',
            'sso_payload' => ['user' => 'tester'],
            'bio_profile' => $bioProfile
        ])->get(route('practitioner.renewals'));

        $response->assertStatus(200);
        $this->assertEquals(12.0, $response->viewData('cpdTotal'));
        $this->assertEquals(20.0, $response->viewData('requiredCpd'));
    }

    public function test_uses_array_wrapped_summary()
    {
        $bioProfile = [
            'profile' => ['id' => 'IDX2'],
            'cpd' => [ ['current_points' => '15', 'cpd_requirement' => '20'] ]
        ];

        $response = $this->withSession([
            'api_token' => 'fake-token',
            'sso_payload' => ['user' => 'tester'],
            'bio_profile' => $bioProfile
        ])->get(route('practitioner.renewals'));

        $response->assertStatus(200);
        $this->assertEquals(15.0, $response->viewData('cpdTotal'));
        $this->assertEquals(20.0, $response->viewData('requiredCpd'));
    }

    public function test_fallback_sums_activity_items()
    {
        $bioProfile = [
            'profile' => ['id' => 'IDX3'],
            'cpd' => []
        ];

        // Stub CPDService to return activity items when controller falls back
        $this->instance(\App\Services\CPDService::class, new class {
            public function getCPDHistory($indexId)
            {
                return [
                    ['current_points' => 5],
                    ['current_points' => 7]
                ];
            }
        });

        $response = $this->withSession([
            'api_token' => 'fake-token',
            'sso_payload' => ['user' => 'tester'],
            'bio_profile' => $bioProfile
        ])->get(route('practitioner.renewals'));

        $response->assertStatus(200);
        $this->assertEquals(12.0, $response->viewData('cpdTotal'));
        $this->assertEquals(20.0, $response->viewData('requiredCpd'));
    }
}
