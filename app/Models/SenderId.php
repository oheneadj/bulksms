<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenderId extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'status', 'reason', 'user_id', 'purpose'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approve()
    {
        $this->update([
            'status' => 'payment_pending',
            'reason' => null
        ]);
    }

    public function reject($reason)
    {
        $this->update([
            'status' => 'rejected',
            'reason' => $reason
        ]);
    }
}
