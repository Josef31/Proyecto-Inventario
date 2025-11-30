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
        'exchange_rate_id',
        'initial_amount_moneda1',
        'cash_sales_moneda1',
        'final_amount_moneda1',
        'initial_amount_moneda2',
        'cash_sales_moneda2',
        'final_amount_moneda2',
        'status',
        'notes'
    ];

    protected $casts = [
        'initial_amount_moneda1' => 'decimal:2',
        'cash_sales_moneda1' => 'decimal:2',
        'final_amount_moneda1' => 'decimal:2',
        'initial_amount_moneda2' => 'decimal:2',
        'cash_sales_moneda2' => 'decimal:2',
        'final_amount_moneda2' => 'decimal:2',
    ];

    /**
     * Relación con el usuario (cajero)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el tipo de cambio
     */
    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
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
        return $query->whereDate('created_at', today());
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
     * Calcular el monto esperado en moneda 1
     */
    public function calculateExpectedAmountMoneda1()
    {
        return $this->initial_amount_moneda1 + $this->cash_sales_moneda1;
    }

    /**
     * Calcular el monto esperado en moneda 2
     */
    public function calculateExpectedAmountMoneda2()
    {
        return $this->initial_amount_moneda2 + $this->cash_sales_moneda2;
    }

    /**
     * Calcular la diferencia en moneda 1
     */
    public function calculateDifferenceMoneda1()
    {
        if ($this->final_amount_moneda1 !== null) {
            return $this->final_amount_moneda1 - $this->calculateExpectedAmountMoneda1();
        }
        return 0;
    }

    /**
     * Calcular la diferencia en moneda 2
     */
    public function calculateDifferenceMoneda2()
    {
        if ($this->final_amount_moneda2 !== null) {
            return $this->final_amount_moneda2 - $this->calculateExpectedAmountMoneda2();
        }
        return 0;
    }
}