<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SenderId>
 */
class SenderIdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => strtoupper(fake()->lexify('??????')),
            'status' => 'pending',
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
