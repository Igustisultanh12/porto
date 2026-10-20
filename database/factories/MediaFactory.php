<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        return ['slot' => 'pdf', 'path' => 'media/test.pdf', 'name' => 'test.pdf', 'mime' => 'application/pdf', 'size' => 10];
    }
}
