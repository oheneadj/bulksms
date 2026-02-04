<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
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
            'phone' => fake()->e164PhoneNumber(),
            'email' => fake()->safeEmail(),
            'country_code' => fake()->countryCode(),
            'tenant_id' => \App\Models\Tenant::factory(), // Assuming Tenant factory exists
            'group_id' => null,
            'is_unsubscribed' => fake()->boolean(10), // 10% chance of being unsubscribed
            'created_by_user_id' => \App\Models\User::factory(),
        ];
    }
}
