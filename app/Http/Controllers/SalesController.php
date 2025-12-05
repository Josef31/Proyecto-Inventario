<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleService;
use App\Models\BusinessService;
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

        $services = BusinessService::select(
            'id',
            'name',
            'customer_rate AS price'
        )->where('is_active', true)->get();

        $paymentMethods = PaymentMethod::select('id', 'name', 'currency')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $openCashRegister = CashRegister::getOpenCashRegister();
        
        // Get latest exchange rate
        $latestExchangeRate = \App\Models\ExchangeRate::orderBy('date', 'desc')->first();
        $exchangeRate = $latestExchangeRate ? $latestExchangeRate->rate : 1;

        return view('sales.index', compact('products', 'services', 'paymentMethods', 'customers', 'openCashRegister', 'exchangeRate'));
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
            'customer_id' => 'required|exists:customers,id',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_method,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.currency' => 'required|in:Bs,USD',
            'total' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'services' => 'nullable|array',
            'services.*.service_id' => 'required_with:services|exists:business_services,id',
            'services.*.quantity' => 'required_with:services|integer|min:1',
            'services.*.price' => 'required_with:services|numeric|min:0'
        ], [
            'customer_id.required' => 'Debe seleccionar un cliente',
            'customer_id.exists' => 'El cliente seleccionado no existe',
            'payments.required' => 'Debe agregar al menos un método de pago',
            'payments.min' => 'Debe agregar al menos un método de pago',
            'payments.*.payment_method_id.required' => 'Método de pago requerido',
            'payments.*.amount.required' => 'Monto de pago requerido',
            'payments.*.amount.min' => 'El monto debe ser mayor a 0',
            'payments.*.currency.required' => 'Moneda requerida',
            'total.required' => 'El total de la venta es requerido',
            'total.min' => 'El total debe ser mayor a 0',
            'items.*.product_id.exists' => 'Uno de los productos seleccionados no existe',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1',
            'services.*.service_id.exists' => 'Uno de los servicios seleccionados no existe',
            'services.*.quantity.min' => 'La cantidad del servicio debe ser al menos 1',
        ]);

        // Validar que haya al menos items o services
        if (empty($validated['items']) && empty($validated['services'])) {
            return response()->json([
                'error' => 'Debe agregar al menos un producto o servicio'
            ], 400);
        }

        // Verificar si hay caja abierta
        $cashRegister = CashRegister::getOpenCashRegister();
        if (!$cashRegister) {
            return response()->json([
                'error' => 'No hay caja abierta. Debe abrir una caja antes de realizar ventas.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Si hay productos, verificar stock
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    
                    if (!$product) {
                        throw new \Exception('Producto no encontrado: ID ' . $item['product_id']);
                    }

                    if ($product->stock_initial < $item['quantity']) {
                        throw new \Exception('Stock insuficiente para: ' . $product->name);
                    }
                }
            }

            // Calcular subtotal e impuestos
            $subtotal = 0;
            
            // Sumar productos
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }
            }
            
            // Sumar servicios
            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $service) {
                    $subtotal += $service['price'] * $service['quantity'];
                }
            }
            
            $taxes = $subtotal * 0.16; // IVA 16%
            $total = $subtotal + $taxes;

            // Obtener tasa de cambio
            $exchangeRate = $cashRegister->exchangeRate->rate ?? 1;

            // Calcular total pagado convirtiendo todo a USD primero
            $totalPaidUSD = 0;
            foreach ($validated['payments'] as $payment) {
                if ($payment['currency'] === 'USD') {
                    $totalPaidUSD += $payment['amount'];
                } else {
                    // Convertir Bs a USD
                    $totalPaidUSD += ($payment['amount'] / $exchangeRate);
                }
            }
            
            // Validar que el total pagado sea suficiente (comparar en USD)
            if ($totalPaidUSD < $total) {
                return response()->json([
                    'error' => 'El monto pagado es insuficiente. Total: $' . number_format($total, 2) . ' USD, Pagado: $' . number_format($totalPaidUSD, 2) . ' USD'
                ], 400);
            }
            
            $change = $totalPaidUSD - $total;

            // Crear la venta (usar primer método de pago como principal para compatibilidad)
            $primaryPayment = $validated['payments'][0];
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'cash_register_id' => $cashRegister->id,
                'customer_id' => $validated['customer_id'],
                'payment_currency' => $primaryPayment['currency'],
                'exchange_rate_used' => $exchangeRate,
                'taxes' => $taxes,
                'payment_method_id' => $primaryPayment['payment_method_id'],
                'amount_received' => $totalPaidUSD,
                'change' => $change,
                'status' => 'completada',
                'invoice_printed' => false
            ]);

            // Crear los registros de pagos
            foreach ($validated['payments'] as $payment) {
                \App\Models\SalePayment::create([
                    'sale_id' => $sale->invoice_number,
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'currency' => $payment['currency']
                ]);
            }

            // Crear los items de la venta y actualizar stock (si hay productos)
            if (!empty($validated['items'])) {
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
            }

            // Crear los servicios de la venta (si hay servicios)
            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $service) {
                    SaleService::create([
                        'sale_id' => $sale->invoice_number,
                        'business_service_id' => $service['service_id'],
                        'quantity' => $service['quantity'],
                        'price' => $service['price']
                    ]);
                }
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
