<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DemoTest;

class DemoTestFactory extends Factory
{
    protected $model = DemoTest::class;

    public function definition()
    {
        return [
            'ref' => 'T-' . $this->faker->unique()->numberBetween(1, 2000),
            'name' => $this->faker->word,
            'description' => $this->faker->text,
            'status' => $this->faker->randomElement(['NEW', 'UPDATED']),
            'is_active' => $this->faker->boolean
        ];
    }
}
