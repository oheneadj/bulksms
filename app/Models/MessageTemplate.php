<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'name',
        'body',
    ];
}
