<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedBirthday extends Model
{
    protected $fillable = [
        'tenant_id',
        'contact_id',
        'year',
        'message_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
