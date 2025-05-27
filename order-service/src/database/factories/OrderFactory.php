<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "address" => fake()->address(),
            "phone" => fake()->phoneNumber(),
            "email" => fake()->email(),
            "status" => fake()->randomElement(["completed", "cancelled"]),
            "products" => [
                [
                    "id" => fake()->numberBetween(1, 100),
                    "name" => fake()->word(),
                    "price" => fake()->randomNumber(4, 5000),
                    "quantity" => fake()->numberBetween(1, 5),
                ],
                [
                    "id" => fake()->numberBetween(1, 100),
                    "name" => fake()->word(),
                    "price" => fake()->randomNumber(4, 5000),
                    "quantity" => fake()->numberBetween(1, 5),
                ]
            ]
        ];
    }
}
