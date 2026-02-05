<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password');

        if (User::where('email', $email)->exists()) {
            $this->command->info("User with email {$email} already exists. Skipping.");
            return;
        }

        // Create a system tenant if one doesn't exist for the super admin
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'system'],
            [
                'name' => 'System Tenant',
                'email' => $email,
                'plan_type' => 'system',
                'status' => 'active',
                'sms_credits' => 999999,
                'monthly_message_count' => 999999,
            ]
        );

        $user = User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'status' => 'active',
            'tenant_id' => $tenant->id,
            'is_account_owner' => true,
            'can_topup_credits' => true,
            'can_view_billing' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info("Super Admin created successfully.");
        $this->command->info("Email: {$email}");
        $this->command->info("Password: {$password}");
    }
}
