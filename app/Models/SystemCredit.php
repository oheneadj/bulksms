<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'balance',
        'total_purchased',
        'total_sold',
    ];
}
