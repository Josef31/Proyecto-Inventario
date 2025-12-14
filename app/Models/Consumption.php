<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'reason',
        'notes',
        'user_id',
        'consumption_date'
    ];

    protected $casts = [
        'consumption_date' => 'date',
        'quantity' => 'decimal:2'
    ];

    // Relación con producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
