<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'initial_amount',
        'final_amount',
        'expected_amount',
        'cash_sales',
        'difference',
        'status',
        'opened_at',
        'closed_at',
        'notes'
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime'
    ];

    /**
     * Relación con el usuario (cajero)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para cajas abiertas
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'abierta');
    }

    /**
     * Scope para cajas cerradas
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'cerrada');
    }

    /**
     * Scope para cajas del día actual
     */
    public function scopeToday($query)
    {
        return $query->whereDate('opened_at', today());
    }

    /**
     * Verificar si hay una caja abierta
     */
    public static function hasOpenCashRegister()
    {
        return static::open()->exists();
    }

    /**
     * Obtener la caja abierta actual
     */
    public static function getOpenCashRegister()
    {
        return static::open()->first();
    }

    /**
     * Calcular el monto esperado
     */
    public function calculateExpectedAmount()
    {
        return $this->initial_amount + $this->cash_sales;
    }

    /**
     * Calcular la diferencia
     */
    public function calculateDifference()
    {
        if ($this->final_amount && $this->expected_amount) {
            return $this->final_amount - $this->expected_amount;
        }
        return 0;
    }
}