<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'invited_by_user_id',
        'email',
        'role',
        'token',
        'can_topup_credits',
        'can_view_billing',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'can_topup_credits' => 'boolean',
        'can_view_billing' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
