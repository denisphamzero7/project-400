<?php

namespace Database\Factories;

use App\Enums\CustomersStatusEnum;
use App\Models\CustomersModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomersModel>
 */
class CustomersModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'loyalty_points' => fake()->numberBetween(0, 1000),
            'status' => fake()->randomElement(CustomersStatusEnum::cases()),
        ];
    }
}
