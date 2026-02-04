<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdaySetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'is_enabled',
        'message_template_id',
        'sender_id',
        'send_time',
        'last_run_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_run_at' => 'datetime',
        'send_time' => 'datetime', // Use datetime cast for time column in Laravel if simple access needed, or string
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }
}
