<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create Standard User
$user = User::firstOrCreate(
    ['email' => 'testuser@example.com'],
    [
        'name' => 'Browser Test User',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]
);
// Ensure tenant exists
if (!$user->tenant) {
    $user->tenant()->create(['name' => 'Test Tenant']);
    $user->refresh();
}

// Create Admin User
$admin = User::firstOrCreate(
    ['email' => 'admin@example.com'],
    [
        'name' => 'Browser Admin',
        'password' => Hash::make('password'),
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]
);

echo "Users Created: testuser@example.com / admin@example.com (password)\n";
