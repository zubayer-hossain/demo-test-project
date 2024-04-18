<?php

namespace App\Services;

use App\Enums\DemoTestInquiryStatus;
use App\Jobs\DispatcherJob;
use App\Models\DemoTest;
use App\Models\DemoTestInquiry;
use Illuminate\Http\Response;

class DemoTestService
{
    /**
     * Create a new inquiry and dispatch jobs.
     * @param array $data
     * @return array
     */
    public function handleInquiry(array $data): array
    {
        try {
            $inquiry = $this->createInquiry($data);
            dispatch(new DispatcherJob($inquiry->id));
            return [
                'body' => ['message' => 'Inquiry processed and jobs dispatched.'],
                'status' => Response::HTTP_CREATED
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to process inquiry: ' . $e->getMessage());
            return [
                'body' => ['message' => 'Failed to process inquiry.'],
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * Create a new inquiry.
     * @param array $data
     * @return DemoTestInquiry
     */
    private function createInquiry(array $data): DemoTestInquiry
    {
        return DemoTestInquiry::create([
            'payload' => json_encode($data),
            'items_total_count' => count($data),
            'status' => DemoTestInquiryStatus::ACTIVE
        ]);
    }

    /**
     * @param $ref
     * @return string[]
     */
    public function activateTest($ref): array
    {
        try {
            DemoTest::where('ref', $ref)->update(['is_active' => true]);
            return [
                'body' => ['message' => 'Test activated successfully.'],
                'status' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            \Log::error("Failed to activate test: {$e->getMessage()}");
            return [
                'body' => ['message' => 'Test activation failed.'],
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * @param $ref
     * @return string[]
     */
    public function deactivateTest($ref): array
    {
        try {
            DemoTest::where('ref', $ref)->update(['is_active' => false]);
            return [
                'body' => ['message' => 'Test deactivated successfully.'],
                'status' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            \Log::error("Failed to deactivate test: {$e->getMessage()}");
            return [
                'body' => ['message' => 'Test deactivation failed.'],
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }
}
