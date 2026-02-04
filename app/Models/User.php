<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'status',
        'is_account_owner',
        'can_topup_credits',
        'can_view_billing',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'created_by_user_id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'created_by_user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function senderIds()
    {
        return $this->hasMany(SenderId::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Role and Permission Helpers
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isTenantAdmin(): bool
    {
        return $this->role === 'tenant_admin' || $this->isSuperAdmin();
    }

    public function isRegularUser(): bool
    {
        return $this->role === 'user';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canTopupCredits(): bool
    {
        return $this->can_topup_credits || $this->isTenantAdmin();
    }

    public function canViewBilling(): bool
    {
        return $this->can_view_billing || $this->isTenantAdmin();
    }
}
