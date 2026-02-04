<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'plan_type',
        'status',
        'sms_credits',
        'monthly_message_count',
        'simulate_webhooks',
    ];

    protected $casts = [
        'sms_credits' => 'integer',
        'monthly_message_count' => 'integer',
        'simulate_webhooks' => 'boolean',
    ];

    public function messageTemplates()
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Check if tenant has enough credits
     */
    public function hasSufficientCredits(int $amount): bool
    {
        return $this->sms_credits >= $amount;
    }

    /**
     * Deduct credits safely
     * 
     * @throws \Exception
     */
    public function deductCredits(int $amount): bool
    {
        if (!$this->hasSufficientCredits($amount)) {
            return false;
        }

        $this->decrement('sms_credits', $amount);
        return true;
    }
}
