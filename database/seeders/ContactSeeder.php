<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure a tenant exists
        $tenant = \App\Models\Tenant::first() ?? \App\Models\Tenant::factory()->create();

        // Ensure a user exists for this tenant
        $user = \App\Models\User::where('tenant_id', $tenant->id)->first() ?? \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);

        // Create groups
        $groups = \App\Models\Group::factory()
            ->count(3)
            ->for($tenant)
            ->for($user, 'creator')
            ->create();

        // Create contacts for each group
        foreach ($groups as $group) {
            \App\Models\Contact::factory()
                ->count(10)
                ->for($tenant)
                ->for($group)
                ->for($user, 'creator')
                ->create();
        }

        // Create some orphan contacts (no group)
        \App\Models\Contact::factory()
            ->count(5)
            ->for($tenant)
            ->for($user, 'creator')
            ->create();
    }
}
