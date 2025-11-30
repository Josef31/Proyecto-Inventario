<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Tu artículo',
                'id_classification' => 1,
                'price_buy' => 10.00,
                'price_sell' => 15.50,
                'stock_initial' => 0, // Cambiado de stock_actual a stock_initial
                'stock_minimum' => 0,
                'expiration_date' => null,
            ],
            [
                'name' => 'Cartulina',
                'id_classification' => 2,
                'price_buy' => 150.00,
                'price_sell' => 200.00,
                'stock_initial' => 0, // Cambiado de stock_actual a stock_initial
                'stock_minimum' => 1,
                'expiration_date' => null,
            ],
            [
                'name' => 'Palas',
                'id_classification' => 5,
                'price_buy' => 180.00,
                'price_sell' => 250.00,
                'stock_initial' => 10, // Cambiado de stock_actual a stock_initial
                'stock_minimum' => 1,
                'expiration_date' => null,
            ],
            [
                'name' => 'Papel opalina',
                'id_classification' => 2,
                'price_buy' => 6.50,
                'price_sell' => 10.00,
                'stock_initial' => 32, // Cambiado de stock_actual a stock_initial
                'stock_minimum' => 1,
                'expiration_date' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}