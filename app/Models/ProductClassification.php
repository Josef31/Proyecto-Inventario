<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductClassification extends Model
{
    use HasFactory;

    protected $table = 'products_classification';
    
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * Relación: Una clasificación tiene muchos productos
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'id_classification', 'id');
    }
}
