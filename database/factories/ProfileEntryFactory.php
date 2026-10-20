<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileEntryFactory extends Factory
{
    public function definition(): array
    {
        return ['kind' => 'skill', 'title' => fake()->word(), 'sort_order' => 0];
    }
}
