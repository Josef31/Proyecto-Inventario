<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CashRegister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    // Mostrar vista de inventario / ventas
    public function index()
    {
        $products = Product::select(
            'id',
            'name',
            'price_sell AS price',
            'stock_initial AS stock'
        )->get();

        $paymentMethods = PaymentMethod::all();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $openCashRegister = CashRegister::getOpenCashRegister();

        return view('sales.index', compact('products', 'paymentMethods', 'customers', 'openCashRegister'));
    }

    // Obtener productos para DataTable
    public function getProducts()
    {
        $products = Product::select(
            'id',
            'name',
            'price_sell',
            'stock_initial'
        )->get();

        return response()->json($products);
    }

    // Registrar venta
    public function processSale(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method_id' => 'required|exists:payment_method,id',
            'payment_currency' => 'required|in:Bs,USD',
            'amount_received' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        // Verificar si hay caja abierta
        $cashRegister = CashRegister::getOpenCashRegister();
        if (!$cashRegister) {
            return response()->json([
                'error' => 'No hay caja abierta. Debe abrir una caja antes de realizar ventas.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Verificar stock de todos los productos
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    throw new \Exception('Producto no encontrado: ID ' . $item['product_id']);
                }

                if ($product->stock_initial < $item['quantity']) {
                    throw new \Exception('Stock insuficiente para: ' . $product->name);
                }
            }

            // Calcular subtotal e impuestos
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $taxes = $subtotal * 0.16; // IVA 16%
            $total = $subtotal + $taxes;

            // Calcular cambio
            $change = $validated['amount_received'] - $total;

            // Crear la venta
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'cash_register_id' => $cashRegister->id,
                'customer_id' => $validated['customer_id'],
                'payment_currency' => $validated['payment_currency'],
                'exchange_rate_used' => $cashRegister->exchangeRate->rate ?? null,
                'taxes' => $taxes,
                'payment_method_id' => $validated['payment_method_id'],
                'amount_received' => $validated['amount_received'],
                'change' => $change,
                'status' => 'completada',
                'invoice_printed' => false
            ]);

            // Crear los items de la venta y actualizar stock
            foreach ($validated['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->invoice_number,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Actualizar stock
                $product = Product::find($item['product_id']);
                $product->stock_initial -= $item['quantity'];
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada correctamente',
                'sale_code' => $sale->sale_code,
                'invoice_number' => $sale->invoice_number,
                'change' => $change
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Obtener ventas del día
    public function getTodaySales()
    {
        $sales = Sale::whereDate('created_at', today())
            ->with(['customer', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($sales);
    }
}
