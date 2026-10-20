<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContentFactory extends Factory
{
    public function definition(): array
    {
        return ['type' => 'project', 'title' => fake()->sentence(4), 'slug' => Str::uuid()->toString(), 'summary' => fake()->sentence(), 'body' => '## Penjelasan'."\n\n".'Isi contoh untuk pengujian.', 'status' => 'draft', 'featured' => false, 'sort_order' => 0, 'metadata' => []];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published', 'published_at' => now()]);
    }
}
