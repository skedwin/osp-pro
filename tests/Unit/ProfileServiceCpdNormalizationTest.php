<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\ProfileService;
use App\Models\User;

class ProfileServiceCpdNormalizationTest extends TestCase
{
    public function test_summary_object_normalized()
    {
        Http::fake(['*' => Http::response([
            'profile' => ['Name' => 'Test'],
            'cpd' => ['current_points' => '8', 'cpd_requirement' => '20']
        ], 200)]);

        $user = new User();
        $user->id = 1001;
        $user->api_token = 'fake-token';

        $service = new ProfileService();
        $result = $service->getFormattedProfile($user);

        $this->assertArrayHasKey('cpd', $result);
        $this->assertIsArray($result['cpd']);
        // summary should be wrapped into array
        $this->assertEquals('8', $result['cpd'][0]['current_points']);
        $this->assertArrayHasKey('cpd_summary', $result);
        $this->assertEquals('8', $result['cpd_summary']['current_points']);
    }

    public function test_array_wrapped_summary_preserved()
    {
        Http::fake(['*' => Http::response([
            'profile' => ['Name' => 'Test'],
            'cpd' => [ ['current_points' => '15', 'cpd_requirement' => '20'] ]
        ], 200)]);

        $user = new User();
        $user->id = 1002;
        $user->api_token = 'fake-token';

        $service = new ProfileService();
        $result = $service->getFormattedProfile($user);

        $this->assertEquals('15', $result['cpd'][0]['current_points']);
        $this->assertEquals('15', $result['cpd_summary']['current_points']);
    }

    public function test_activity_list_normalized_and_summary_empty()
    {
        Http::fake(['*' => Http::response([
            'profile' => ['Name' => 'Test'],
            'cpd' => [ ['current_points' => '4'], ['current_points' => '6'] ]
        ], 200)]);

        $user = new User();
        $user->id = 1003;
        $user->api_token = 'fake-token';

        $service = new ProfileService();
        $result = $service->getFormattedProfile($user);

        $this->assertCount(2, $result['cpd']);
        $this->assertEquals([], $result['cpd_summary']);
    }
}
