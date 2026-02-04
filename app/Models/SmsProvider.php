<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'config',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'config' => 'encrypted:array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
}
