<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessService extends Model
{
    use HasFactory;

    protected $table = 'business_services';

    protected $fillable = [
        'name',
        'description',
        'base_cost',
        'customer_rate',
        'estimated_duration',
        'is_active'
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'customer_rate' => 'decimal:2',
        'estimated_duration' => 'integer',
        'is_active' => 'boolean'
    ];

    public function getEstimatedProfitAttribute()
    {
        return $this->customer_rate - $this->base_cost;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->base_cost == 0) return 0;
        return (($this->customer_rate - $this->base_cost) / $this->base_cost) * 100;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
    }
}