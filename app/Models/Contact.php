<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'group_id',
        'title',
        'first_name',
        'surname',
        'phone',
        'email',
        'dob',
        'country_code',
        'metadata',
        'is_unsubscribed',
        'unsubscribed_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'dob' => 'date',
        'is_unsubscribed' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    public function getNameAttribute(): string
    {
        return trim("{$this->title} {$this->first_name} {$this->surname}");
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'contact_group');
    }
}
