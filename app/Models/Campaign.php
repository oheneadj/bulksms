<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'sender_id',
        'message_body',
        'total_recipients',
        'total_cost',
        'status',
        'scheduled_at',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
