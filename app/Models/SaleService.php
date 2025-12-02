<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleService extends Model
{
    use HasFactory;

    protected $table = 'sale_services';

    // No tiene columna id, usa clave primaria compuesta
    public $incrementing = false;
    protected $primaryKey = ['sale_id', 'business_service_id'];

    protected $fillable = [
        'sale_id',
        'business_service_id',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    /**
     * Relación con la venta
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'invoice_number');
    }

    /**
     * Relación con el servicio
     */
    public function service()
    {
        return $this->belongsTo(BusinessService::class, 'business_service_id');
    }

    /**
     * Override para setear la clave primaria compuesta
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the value of the model's primary key for save query
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}
