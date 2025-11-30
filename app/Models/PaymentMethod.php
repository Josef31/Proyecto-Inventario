<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_method';
    
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * Relación: Un método de pago tiene muchas ventas
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'payment_method_id', 'id');
    }
}
