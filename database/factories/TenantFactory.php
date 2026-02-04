<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
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
            'slug' => fake()->slug(),
            'email' => fake()->companyEmail(),
            'plan_type' => 'free',
            'status' => 'active',
            'sms_credits' => 10,
            'monthly_message_count' => 0,
        ];
    }
}
