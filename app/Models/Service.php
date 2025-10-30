<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_cost',
        'client_rate',
        'duration'
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'client_rate' => 'decimal:2'
    ];
}