<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'base_currency',
        'target_currency',
        'rate'
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'date' => 'date'
    ];

    /**
     * Relación: Una tasa de cambio puede ser usada en múltiples cajas
     */
    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    /**
     * Scope para obtener tasas USD a Bs
     */
    public function scopeUsdToBs($query)
    {
        return $query->where('base_currency', 'USD')
                    ->where('target_currency', 'VES'); // VES = Bolívar Venezolano
    }

    /**
     * Obtener la tasa de cambio más reciente
     */
    public static function getLatestRate()
    {
        return static::usdToBs()->orderBy('date', 'desc')->orderBy('created_at', 'desc')->first();
    }
}
