<?php

namespace Database\Factories;

use App\Models\SmsProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmsProviderFactory extends Factory
{
    protected $model = SmsProvider::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'provider' => $this->faker->randomElement(['twilio', 'mnotify', 'hubtel', 'messagebird']),
            'config' => [],
            'priority' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
