<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DemoTestInquiry;

class DemoTestInquiryFactory extends Factory
{
    protected $model = DemoTestInquiry::class;

    public function definition()
    {
        return [
            'payload' => json_encode([]),
            'status' => 'ACTIVE',
            'items_total_count' => $this->faker->randomDigitNotNull,
            'items_processed_count' => 0,
            'items_failed_count' => 0
        ];
    }
}
