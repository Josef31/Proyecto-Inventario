<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_code',
        'invoice_number', // Nuevo campo
        'user_id',
        'customer_name',
        'customer_rfc',
        'subtotal',
        'taxes',
        'total',
        'payment_method',
        'amount_received',
        'change',
        'status',
        'invoice_printed', // Nuevo campo
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'taxes' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change' => 'decimal:2',
        'invoice_printed' => 'boolean'
    ];

    /**
     * Relación con el usuario (vendedor)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los items de la venta
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Boot del modelo para generar código de venta automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->sale_code)) {
                $sale->sale_code = 'V-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
            
            // Generar número de factura si no existe
            if (empty($sale->invoice_number)) {
                $sale->invoice_number = 'F-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Scope para ventas completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completada');
    }

    /**
     * Scope para ventas del día actual
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Obtener el total de items en la venta
     */
    public function getItemsCountAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Marcar factura como impresa
     */
    public function markAsPrinted()
    {
        $this->update(['invoice_printed' => true]);
    }

    /**
     * Obtener el nombre del cliente formateado
     */
    public function getCustomerNameFormattedAttribute()
    {
        return $this->customer_name ?: 'Cliente General';
    }

    /**
     * Obtener el método de pago formateado
     */
    public function getPaymentMethodFormattedAttribute()
    {
        return strtoupper($this->payment_method);
    }
}