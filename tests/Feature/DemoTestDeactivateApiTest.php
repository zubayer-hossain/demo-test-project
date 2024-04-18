<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\DemoTest;

class DemoTestDeactivateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivation_of_active_demo_test()
    {
        $demoTest = DemoTest::factory()->create([
            'is_active' => true
        ]);

        $response = $this->postJson(route('demo.test.deactivate'), ['ref' => $demoTest->ref]);
        $response->assertOk();
        $response->assertJson(['message' => 'Test deactivated successfully.']);

        $this->assertDatabaseHas('demo_test', [
            'ref' => $demoTest->ref,
            'is_active' => false
        ]);
    }

    public function test_deactivation_of_already_inactive_demo_test_triggers_validation_error()
    {
        $demoTest = DemoTest::factory()->create([
            'is_active' => false
        ]);

        $response = $this->postJson(route('demo.test.deactivate'), ['ref' => $demoTest->ref]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'ref' => ['The test with the given reference ID is already inactive.']
            ]
        ]);
    }
}
