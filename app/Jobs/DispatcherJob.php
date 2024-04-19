<?php

namespace App\Jobs;

use App\Models\DemoTestInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $inquiryId;

    /**
     * Create a new job instance.
     *
     * @param int $inquiryId
     */
    public function __construct(int $inquiryId)
    {
        $this->inquiryId = $inquiryId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Find the inquiry by ID and if not found, log an error
        $inquiry = DemoTestInquiry::find($this->inquiryId);
        if (!$inquiry) {
            \Log::error("Inquiry not found: ID {$this->inquiryId}");
            return;
        }

        // Decode the payload and dispatch jobs for each item
        $items = $inquiry->payload;
        $totalItems = count($items);
        $isSimulateFailure = config('app.simulate_failure');
        // Calculate the number of items that should fail based on the simulation configuration
        $failCount = $isSimulateFailure ? (int) floor(0.1 * $totalItems) : 0;

        for ($i = 0; $i < $totalItems; $i++) {
            $shouldFail = $isSimulateFailure && ($i < $failCount);
            dispatch(new ProcessDemoTestJob($items[$i], $this->inquiryId, $shouldFail));
        }
    }
}
