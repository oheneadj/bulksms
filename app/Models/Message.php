<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign; // Added this line to import Campaign model

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'campaign_id', // Added campaign_id
        'sender_id',
        'recipient',
        'body',
        'parts',
        'cost',
        'status',
        'gateway_message_id',
        'scheduled_at',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
