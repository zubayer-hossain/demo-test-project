<?php

namespace Database\Factories;

use App\Enums\DemoTestStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DemoTest;

class DemoTestFactory extends Factory
{
    protected $model = DemoTest::class;

    public function definition()
    {
        return [
            'ref' => 'T-' . $this->faker->unique()->numberBetween(1, 2001),
            'name' => $this->faker->word,
            'description' => $this->faker->text,
            'status' => $this->faker->randomElement([DemoTestStatus::NEW, DemoTestStatus::UPDATED]),
            'is_active' => $this->faker->boolean
        ];
    }
}
