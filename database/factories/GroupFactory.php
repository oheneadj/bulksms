<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->sentence(),
            'contacts_count' => 0,
            'tenant_id' => \App\Models\Tenant::factory(), // Assuming Tenant factory exists or handled by seeder
            'created_by_user_id' => \App\Models\User::factory(),
        ];
    }
}
