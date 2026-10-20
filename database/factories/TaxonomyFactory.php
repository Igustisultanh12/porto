<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaxonomyFactory extends Factory
{
    public function definition(): array
    {
        return ['kind' => 'tag', 'name' => fake()->unique()->word()];
    }
}
