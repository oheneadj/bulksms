<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'credits',
        'unit_price',
        'price',
        'currency',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unit_price' => 'decimal:4',
        'price' => 'decimal:2',
    ];
}
