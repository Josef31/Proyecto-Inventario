<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    public function index()
    {
        return view('sales.index');
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('search');
        
        $products = Product::where('name', 'LIKE', "%{$search}%")
                          ->orWhere('code', 'LIKE', "%{$search}%")
                          ->where('stock', '>', 0)
                          ->get(['id', 'name', 'code', 'price', 'stock']);
        
        return response()->json($products);
    }

    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_rfc' => 'nullable|string|max:20',
            'payment_method' => 'required|in:efectivo,tarjeta',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calcular totales
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $taxes = $subtotal * 0.16; // IVA 16%
            $total = $subtotal + $taxes;

            // Inicializar variables
            $amountReceived = $request->amount_received ?? 0;
            $change = 0;

            // Validar monto recibido para efectivo
            if ($request->payment_method === 'efectivo') {
                if ($amountReceived < $total) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El monto recibido es menor al total a pagar'
                    ], 422);
                }
                $change = $amountReceived - $total;
            } else {
                // Para tarjeta, el monto recibido es igual al total
                $amountReceived = $total;
                $change = 0;
            }

            // Crear la venta (el invoice_number se genera automáticamente en el modelo)
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'customer_name' => $request->customer_name,
                'customer_rfc' => $request->customer_rfc,
                'subtotal' => $subtotal,
                'taxes' => $taxes,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'amount_received' => $amountReceived,
                'change' => $change,
                'status' => 'completada'
            ]);

            // Crear items de la venta y actualizar stock
            foreach ($request->items as $item) {
                $product = Product::find($item['id']);
                
                if (!$product) {
                    throw new \Exception("Producto no encontrado");
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para: {$product->name}");
                }

                // Crear item de venta
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => $product->name,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity']
                ]);

                // Actualizar stock del producto
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'sale_code' => $sale->sale_code,
                'invoice_number' => $sale->invoice_number, // Incluir el número de factura
                'total' => $total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTodaySales()
    {
        $sales = Sale::with('items')
                    ->today()
                    ->completed()
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json($sales);
    }
}