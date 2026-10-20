<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return ['submission_id' => fake()->uuid(), 'name' => 'Penguji', 'email' => 'sender@example.test', 'subject' => 'Diskusi', 'message' => 'Pesan pengujian yang valid.'];
    }
}
