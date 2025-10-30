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
        'stock_initial', // Cambiado de stock_actual a stock_initial
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
     * Verificar si el stock está bajo
     */
    public function getIsLowStockAttribute()
    {
        return $this->stock_initial <= $this->stock_minimum || $this->stock_initial <= 5;
    }

    /**
     * Verificar si está agotado
     */
    public function getIsOutOfStockAttribute()
    {
        return $this->stock_initial <= 0;
    }

    /**
     * Verificar si está próximo a vencer
     */
    public function getIsNearExpirationAttribute()
    {
        if (!$this->expiration_date) {
            return false;
        }
        
        return $this->expiration_date <= Carbon::now()->addMonth();
    }

    /**
     * Obtener la clase CSS para el stock
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
     * Obtener la clase CSS para la fila
     */
    public function getRowClassAttribute()
    {
        if ($this->is_out_of_stock) {
            return 'alerta-vencimiento';
        } elseif ($this->is_low_stock) {
            return 'alerta-stock';
        } elseif ($this->is_near_expiration) {
            return 'alerta-vencimiento';
        }
        return '';
    }

    /**
     * Formatear fecha de vencimiento para mostrar
     */
    public function getFormattedExpirationDateAttribute()
    {
        return $this->expiration_date 
            ? Carbon::parse($this->expiration_date)->format('Y-m-d')
            : 'N/A';
    }

    /**
     * Calcular el valor total invertido en este producto
     */
    public function getTotalInvestmentAttribute()
    {
        return $this->price_buy * $this->stock_initial;
    }

    /**
     * Verificar si el precio de venta cumple con el margen mínimo
     */
    public function isValidMargin($margenMinimo = 0.3)
    {
        $precioMinimo = $this->price_buy * (1 + $margenMinimo);
        return $this->price_sell >= $precioMinimo;
    }

    /**
     * Accessor para mantener compatibilidad (opcional)
     */
    public function getStockActualAttribute()
    {
        return $this->stock_initial;
    }
}