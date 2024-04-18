<?php

namespace App\Jobs;

use App\Models\DemoTest;
use App\Models\DemoTestInquiry;
use App\Enums\DemoTestStatus;
use App\Enums\DemoTestInquiryStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessDemoTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    public int $inquiryId;
    public bool $shouldFail;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @param int $inquiryId
     * @param bool $shouldFail
     */
    public function __construct(array $data, int $inquiryId, bool $shouldFail = false)
    {
        $this->data = $data;
        $this->inquiryId = $inquiryId;
        $this->shouldFail = $shouldFail;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        // Find the inquiry by ID and if not found, log an error
        $inquiry = DemoTestInquiry::find($this->inquiryId);
        if (!$inquiry) {
            \Log::error("Inquiry not found: ID {$this->inquiryId} for ref {$this->data['ref']}");
            return;
        }

        // Simulate failure if the configuration is set
        if ($this->shouldFail) {
            \Log::info("Simulating failure for ref {$this->data['ref']}.");

            // Only increment the failed count once per item
            if ($this->attempts() === 2){
                $inquiry->increment('items_failed_count');
                $this->updateInquiryStatus($inquiry);
            }

            throw new \Exception('Simulated failure');
        }

        try {
            $this->storeDemoTest();
            $inquiry->increment('items_processed_count');
            $this->updateInquiryStatus($inquiry);
        } catch (Throwable $e) {
            $this->handleFailure($e, $inquiry);
        }
    }

    /**
     * Store the demo test in the database.
     */
    private function storeDemoTest(): void
    {
        $demoTest = DemoTest::updateOrCreate(
            ['ref' => $this->data['ref']],
            [
                'name' => $this->data['name'],
                'description' => $this->data['description'],
                'is_active' => true
            ]
        );
        // Set status based on creation or update
        $status = $demoTest->wasRecentlyCreated ? DemoTestStatus::NEW : DemoTestStatus::UPDATED;
        $demoTest->update(['status' => $status]);
    }

    /**
     * Handle the failure of the job.
     *
     * @param Throwable $e
     * @param $inquiry
     * @throws Throwable
     */
    private function handleFailure(Throwable $e, $inquiry): void
    {
        \Log::error("Job failed: " . $e->getMessage());
        if ($this->attempts() >= $this->tries) {
            $inquiry->increment('items_failed_count');
            $this->updateInquiryStatus($inquiry);
            throw $e;  // Re-throw the exception to mark this job as failed
        }
    }

    /**
     * Update the inquiry status based on the processed and failed counts.
     *
     * @param $inquiry
     */
    private function updateInquiryStatus($inquiry): void
    {
        if ($inquiry->items_processed_count + $inquiry->items_failed_count === $inquiry->items_total_count) {
            $inquiry->status = $inquiry->items_failed_count > 0 ? DemoTestInquiryStatus::FAILED : DemoTestInquiryStatus::PROCESSED;
            $inquiry->save();
        }
    }
}
