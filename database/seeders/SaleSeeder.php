<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    public function run()
    {
        // Obtener usuario admin
        $user = User::first();
        
        // Obtener productos disponibles
        $products = Product::all();

        // Verificar que hay productos
        if ($products->isEmpty()) {
            $this->command->error('❌ No hay productos disponibles. Ejecuta ProductSeeder primero.');
            return;
        }

        $sales = [
            [
                'customer_name' => 'Juan Pérez García',
                'customer_rfc' => 'PEGJ800101ABC',
                'payment_method' => 'efectivo',
                'amount_received' => 5000.00,
                'items' => $this->generateItems($products, [1, 2])
            ],
            [
                'customer_name' => 'María Rodríguez López',
                'customer_rfc' => 'ROLM750515XYZ',
                'payment_method' => 'tarjeta',
                'amount_received' => null,
                'items' => $this->generateItems($products, [1, 1])
            ],
            [
                'customer_name' => 'Carlos Hernández',
                'customer_rfc' => null,
                'payment_method' => 'efectivo',
                'amount_received' => 2000.00,
                'items' => $this->generateItems($products, [1, 1])
            ],
            [
                'customer_name' => 'Ana Martínez',
                'customer_rfc' => 'MASA820202DEF',
                'payment_method' => 'tarjeta',
                'amount_received' => null,
                'items' => $this->generateItems($products, [2, 1])
            ],
            [
                'customer_name' => 'Roberto Sánchez',
                'customer_rfc' => null,
                'payment_method' => 'efectivo',
                'amount_received' => 1500.00,
                'items' => $this->generateItems($products, [1, 1, 1])
            ]
        ];

        $createdSales = 0;

        foreach ($sales as $saleData) {
            try {
                DB::transaction(function () use ($saleData, $user, $products, &$createdSales) {
                    // Calcular totales
                    $subtotal = 0;
                    $items = [];
                    
                    foreach ($saleData['items'] as $itemData) {
                        $product = $products->find($itemData['product_id']);
                        
                        if (!$product || $product->stock < $itemData['quantity']) {
                            continue;
                        }

                        $itemSubtotal = $product->price * $itemData['quantity'];
                        $subtotal += $itemSubtotal;
                        
                        $items[] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'price' => $product->price,
                            'quantity' => $itemData['quantity'],
                            'subtotal' => $itemSubtotal
                        ];
                    }
                    
                    // Si no hay items válidos, saltar esta venta
                    if (empty($items)) {
                        return;
                    }

                    $taxes = $subtotal * 0.16;
                    $total = $subtotal + $taxes;
                    $change = $saleData['payment_method'] === 'efectivo' ? 
                             ($saleData['amount_received'] - $total) : 0;

                    // Crear la venta
                    $sale = Sale::create([
                        'user_id' => $user->id,
                        'customer_name' => $saleData['customer_name'],
                        'customer_rfc' => $saleData['customer_rfc'],
                        'subtotal' => $subtotal,
                        'taxes' => $taxes,
                        'total' => $total,
                        'payment_method' => $saleData['payment_method'],
                        'amount_received' => $saleData['amount_received'],
                        'change' => $change,
                        'status' => 'completada',
                        'invoice_printed' => rand(0, 1)
                    ]);

                    // Crear items de la venta
                    foreach ($items as $item) {
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'],
                            'subtotal' => $item['subtotal']
                        ]);

                        // Actualizar stock del producto
                        $product = Product::find($item['product_id']);
                        $product->decrement('stock', $item['quantity']);
                    }

                    $createdSales++;
                });
            } catch (\Exception $e) {
                $this->command->error("Error creando venta: {$e->getMessage()}");
            }
        }

        $this->command->info("✅ {$createdSales} ventas de ejemplo creadas exitosamente.");
    }

    /**
     * Generar items de venta de forma segura
     */
    private function generateItems($products, $quantities)
    {
        $items = [];
        $usedIndices = [];
        
        foreach ($quantities as $quantity) {
            // Encontrar un producto no usado
            $index = 0;
            do {
                $index = array_rand($products->toArray());
            } while (in_array($index, $usedIndices) && count($usedIndices) < $products->count());
            
            $usedIndices[] = $index;
            $product = $products[$index];
            
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity
            ];
        }
        
        return $items;
    }
}