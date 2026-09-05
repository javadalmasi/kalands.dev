<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'title' => fake()->sentence,
        ];
    }
}
