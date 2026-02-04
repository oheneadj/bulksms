<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->registrationRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($input) {
            $fullName = $input['first_name'].' '.$input['last_name'];
            
            // 1. Create Tenant
            $tenant = \App\Models\Tenant::create([
                'name' => $fullName . "'s Team",
                'slug' => \Illuminate\Support\Str::slug($fullName . "'s Team-" . \Illuminate\Support\Str::random(5)),
                'email' => $input['email'],
                'plan_type' => 'free',
            ]);

            // 2. Create User
            return User::create([
                'name' => $fullName,
                'email' => $input['email'],
                'password' => $input['password'],
                'tenant_id' => $tenant->id,
                'role' => 'tenant_admin',
                'status' => 'active',
                'is_account_owner' => true,
                'can_topup_credits' => true,
                'can_view_billing' => true,
            ]);
        });
    }
}
