<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'classification',
        'price_buy',
        'price_sell',
        'stock_initial', 
        'stock_minimum',
        'expiration_date'
    ];

    protected $casts = [
        'price_buy' => 'decimal:2',
        'price_sell' => 'decimal:2',
        'expiration_date' => 'date'
    ];

    /**
     * Scope para productos con stock bajo
     */
    public function scopeLowStock($query)
    {
        return $query->where('stock_initial', '<=', DB::raw('stock_minimum'))
                     ->orWhere('stock_initial', '<=', 5);
    }

    /**
     * Scope para productos agotados
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_initial', '<=', 0);
    }

    /**
     * Scope para productos próximos a vencer
     */
    public function scopeNearExpiration($query)
    {
        return $query->whereNotNull('expiration_date')
                     ->where('expiration_date', '<=', Carbon::now()->addMonth());
    }

    /**
     * Verificar si el stock está bajo (Accessor)
     */
    public function getIsLowStockAttribute()
    {
        return $this->stock_initial <= $this->stock_minimum || $this->stock_initial <= 5;
    }

    /**
     * Verificar si está agotado (Accessor)
     */
    public function getIsOutOfStockAttribute()
    {
        return $this->stock_initial <= 0;
    }

    /**
     * Verificar si está próximo a vencer (Accessor)
     */
    public function getIsNearExpirationAttribute()
    {
        if (!$this->expiration_date) {
            return false;
        }
        
        // Asegura que se compara una instancia de Carbon con otra
        return Carbon::parse($this->expiration_date) <= Carbon::now()->addMonth();
    }

    /**
     * Obtener la clase CSS para el stock (Accessor)
     */
    public function getStockClassAttribute()
    {
        if ($this->is_out_of_stock) {
            return 'stock-agotado';
        } elseif ($this->is_low_stock) {
            return 'stock-bajo';
        }
        return '';
    }

    /**
     * Obtener la clase CSS para la fila (Accessor)
     */
    public function getRowClassAttribute()
    {
        if ($this->is_out_of_stock) {
            return 'alerta-vencimiento'; // Rojo (agotado)
        } elseif ($this->is_low_stock) {
            return 'alerta-stock'; // Amarillo (stock bajo)
        } elseif ($this->is_near_expiration) {
             // Si tiene otra alerta, no la sobreescribe, pero en este caso la alerta de vencimiento
             // es más importante, por lo que la dejamos como una clase que puede aplicar color.
             return 'alerta-vencimiento'; 
        }
        return '';
    }

    /**
     * Formatear fecha de vencimiento para mostrar (Accessor)
     */
    public function getFormattedExpirationDateAttribute()
    {
        return $this->expiration_date 
            ? Carbon::parse($this->expiration_date)->format('Y-m-d')
            : 'N/A';
    }

    /**
     * Calcular el valor total invertido en este producto (Accessor)
     */
    public function getTotalInvestmentAttribute()
    {
        return $this->price_buy * $this->stock_initial;
    }

    /**
     * Accessor para mantener compatibilidad (opcional)
     */
    public function getStockActualAttribute()
    {
        return $this->stock_initial;
    }
}