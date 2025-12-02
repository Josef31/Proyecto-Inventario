<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    // La clave primaria es invoice_number, no id
    protected $primaryKey = 'invoice_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'invoice_number',
        'sale_code',
        'user_id',
        'cash_register_id',
        'customer_id',
        'payment_currency',
        'exchange_rate_used',
        'taxes',
        'payment_method_id',
        'amount_received',
        'change',
        'status',
        'invoice_printed',
        'notes'
    ];

    protected $casts = [
        'taxes' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change' => 'decimal:2',
        'exchange_rate_used' => 'decimal:4',
        'invoice_printed' => 'boolean'
    ];

    /**
     * Relación: Una venta pertenece a un método de pago
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

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
        return $this->hasMany(SaleItem::class, 'sale_id', 'invoice_number');
    }

    /**
     * Relación con el cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con los servicios de la venta
     */
    public function services(): HasMany
    {
        return $this->hasMany(SaleService::class, 'sale_id', 'invoice_number');
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
     * Calcular el subtotal desde los items y servicios
     */
    public function getSubtotalAttribute()
    {
        $itemsTotal = $this->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
        
        $servicesTotal = $this->services->sum('price');
        
        return $itemsTotal + $servicesTotal;
    }

    /**
     * Calcular el total (subtotal + taxes)
     */
    public function getTotalAttribute()
    {
        return $this->subtotal + $this->taxes;
    }

    /**
     * Obtener el método de pago formateado
     */
    public function getPaymentMethodFormattedAttribute()
    {
        return strtoupper($this->payment_method);
    }
}