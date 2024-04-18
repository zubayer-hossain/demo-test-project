<?php

namespace Tests\Feature;

use App\Enums\DemoTestInquiryStatus;
use App\Jobs\DispatcherJob;
use App\Jobs\ProcessDemoTestJob;
use App\Models\DemoTest;
use App\Models\DemoTestInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Http\Response;

class DemoTestApiTest extends TestCase
{
    use RefreshDatabase;

    public string $demoTestApiEndpoint = '/api/demo/test';

    /**
     * Test if the demo test API endpoint exists.
     */
    public function test_demo_test_endpoint_responds_successfully()
    {
        $response = $this->postJson($this->demoTestApiEndpoint, []);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * Test handling of more than 2000 objects to simulate a validation error.
     */
    public function test_handling_of_excessive_objects_triggers_validation_error()
    {
        // Generating more than 2000 objects to exceed the limit
        $data = DemoTest::factory()->count(2001)->make()->toArray();

        // Sending a POST request with the data
        $response = $this->postJson($this->demoTestApiEndpoint, $data);

        // Asserting that the status code is HTTP_UNPROCESSABLE_ENTITY
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Asserting the full response structure including the comprehensive error details
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'items' => ['A maximum of 2000 items are allowed.']
            ]
        ]);
    }

    /**
     * Test ref and name fields are required.
     */
    public function test_ref_and_name_fields_are_required()
    {
        $data = [
            [
                'description' => 'Test Description'
            ]
        ];

        $response = $this->postJson($this->demoTestApiEndpoint, $data);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                '0.ref' => ['The ref is required for each item.'],
                '0.name' => ['The name is required for each item.']
            ]
        ]);
    }

    /**
     * Test multiple validation errors.
     */
    public function test_multiple_validation_errors_in_response()
    {
        // Assuming $data contains the test data that would generate the sample response you provided
        $data = [
            ['name' => 'Test', 'description' => 'Test without ref'], // Missing ref
            ['ref' => 'T-1', 'description' => 'Test with name missing'], // Missing name
            ['ref' => 'T-3', 'name' => 'Duplicate Ref Test', 'description' => 'Test Description'], // Duplicate ref
            ['ref' => 'T-3', 'name' => 'Duplicate Ref Test Again', 'description' => 'Test Description'], // Duplicate ref
            ['ref' => 'czx-fd', 'name' => 'Invalid Format', 'description' => 'Test Description'], // Invalid ref format
        ];

        $response = $this->postJson($this->demoTestApiEndpoint, $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                '0.ref' => ['The ref is required for each item.'],
                '1.name' => ['The name is required for each item.'],
                '2.ref' => ['Duplicate ref found for T-3. Please remove duplicates.'],
                '3.ref' => ['Duplicate ref found for T-3. Please remove duplicates.'],
                '4.ref' => ['Invalid ref format for ref czx-fd. The ref must follow the format T-[number].'],
            ]
        ]);
    }

    /**
     * Test handling of inactive items in the request.
     */
    public function test_handling_inactive_items_triggers_validation_error()
    {
        // Create 4 active items
        $activeItems = DemoTest::factory()->count(4)->create(['is_active' => true]);

        // Create 1 inactive item
        $inactiveItem = DemoTest::factory()->create(['is_active' => false]);

        // Prepare the request data including the inactive item
        $data = $activeItems->map(function ($item) {
            return [
                'ref' => $item->ref,
                'name' => $item->name,
                'description' => $item->description
            ];
        })->toArray();

        // Add the inactive item to the request data
        $data[] = [
            'ref' => $inactiveItem->ref,
            'name' => $inactiveItem->name,
            'description' => $inactiveItem->description
        ];

        // Send a POST request with the data
        $response = $this->postJson($this->demoTestApiEndpoint, $data);

        // Assert that the response status is HTTP_UNPROCESSABLE_ENTITY
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Assert the expected validation error message
        $response->assertJson(
            [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'items' => ['One or more items are inactive and cannot be processed.']
                ]
            ]
        );
    }

    /**
     * Test handling of a valid request with five items and ensure job dispatch.
     */
    public function test_successful_handling_of_valid_request_and_dispatcher_job_dispatch()
    {
        Queue::fake();

        // Create an array of 5 valid items using the factory
        $data = DemoTest::factory()->count(5)->make()->toArray();

        // Adjust the 'ref' to ensure it follows the required format
        foreach ($data as $index => $item) {
            $data[$index]['ref'] = 'T-' . ($index + 1);
        }

        // Send a POST request with the valid data
        $response = $this->postJson($this->demoTestApiEndpoint, $data);

        // Assert that the response has the correct HTTP status and contains the expected message
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson(['message' => 'Inquiry processed and jobs dispatched.']);

        // Assert the job was pushed to the queue
        Queue::assertPushed(DispatcherJob::class);
    }

    /**
     * Test that DispatcherJob dispatches ProcessDemoTestJob for each item.
     */
    public function test_dispatcher_job_dispatches_process_demo_test_jobs()
    {
        $payload = [
            ['ref' => 'T-1', 'name' => 'Test 1', 'description' => 'Description 1'],
            ['ref' => 'T-2', 'name' => 'Test 2', 'description' => 'Description 2'],
            ['ref' => 'T-3', 'name' => 'Test 3', 'description' => 'Description 3']
        ];

        $demoTestInquiry = DemoTestInquiry::create([
            'payload' => json_encode($payload),
            'items_total_count' => count($payload),
            'status' => DemoTestInquiryStatus::ACTIVE
        ]);

        Queue::fake();

        $dispatcherJob = new DispatcherJob($demoTestInquiry->id);
        $dispatcherJob->handle();

        Queue::assertPushed(ProcessDemoTestJob::class, count($payload));
        Queue::assertPushed(ProcessDemoTestJob::class, function ($job) use ($payload) {
            return in_array($job->data, $payload);
        });
    }
}
