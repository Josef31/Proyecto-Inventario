<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashController extends Controller
{
    public function index()
    {
        $openCashRegister = CashRegister::with(['user', 'exchangeRate'])->where('status', 'abierta')->first();
        $closedCashRegisters = CashRegister::with(['user', 'exchangeRate'])
            ->where('status', 'cerrada')
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();
        
        $totalCortes = $closedCashRegisters->count();

        // Obtener tasa de cambio actual
        $currentExchangeRate = ExchangeRate::orderBy('created_at', 'desc')->first();

        // Calcular ventas en efectivo del día si hay caja abierta
        $cashSalesTodayBs = 0;
        $cashSalesTodayUsd = 0;
        
        if ($openCashRegister) {
            // Ventas en Bolívares (todos los métodos de pago EXCEPTO Dólares)
            $salesBs = Sale::with(['items', 'services'])
                ->where('created_at', '>=', $openCashRegister->created_at)
                ->where('payment_method_id', '!=', 1) // Todos excepto Dólares
                ->where('status', 'completada')
                ->get();

            if ($salesBs->isNotEmpty()) {
                // Usar el accessor total del modelo Sale que incluye IVA
                $cashSalesTodayBs = $salesBs->sum('total');
            }

            // Ventas en Dólares (payment_method_id = 1)
            $salesUsd = Sale::with(['items', 'services'])
                ->where('created_at', '>=', $openCashRegister->created_at)
                ->where('payment_method_id', 1) // Dólares
                ->where('status', 'completada')
                ->get();

            if ($salesUsd->isNotEmpty()) {
                // Usar el accessor total del modelo Sale que incluye IVA
                $cashSalesTodayUsd = $salesUsd->sum('total');
            }
        }

        return view('cash.index', compact(
            'openCashRegister', 
            'closedCashRegisters', 
            'totalCortes',
            'cashSalesTodayBs',
            'cashSalesTodayUsd',
            'currentExchangeRate'
        ));
    }

    public function openCashRegister(Request $request)
    {
        $request->validate([
            'initial_amount_bs' => 'required|numeric|min:0',
            'initial_amount_usd' => 'required|numeric|min:0',
            'exchange_rate_id' => 'required|exists:exchange_rates,id'
        ]);

        // Verificar si ya hay una caja abierta
        if (CashRegister::hasOpenCashRegister()) {
            return redirect()->route('cash.index')
                ->with('error', 'Ya hay una caja abierta. Debe cerrar la caja actual antes de abrir una nueva.');
        }

        try {
            DB::beginTransaction();

            $cashRegister = CashRegister::create([
                'user_id' => Auth::id(),
                'exchange_rate_id' => $request->exchange_rate_id,
                'initial_amount_moneda1' => $request->initial_amount_bs,
                'initial_amount_moneda2' => $request->initial_amount_usd,
                'cash_sales_moneda1' => 0,
                'cash_sales_moneda2' => 0,
                'status' => 'abierta'
            ]);

            DB::commit();

            return redirect()->route('cash.index')
                ->with('success', 'Caja abierta exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cash.index')
                ->with('error', 'Error al abrir la caja: ' . $e->getMessage());
        }
    }

    public function closeCashRegister(Request $request)
    {
        $request->validate([
            'final_amount_bs' => 'required|numeric|min:0',
            'final_amount_usd' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $cashRegister = CashRegister::getOpenCashRegister();
            
            if (!$cashRegister) {
                return redirect()->route('cash.index')
                    ->with('error', 'No hay caja abierta para cerrar');
            }

            // Calcular ventas en efectivo desde la apertura de la caja
            // Ventas en Bolívares (todos los métodos EXCEPTO Dólares)
            $salesBs = Sale::where('created_at', '>=', $cashRegister->created_at)
                ->where('payment_method_id', '!=', 1) // Todos excepto Dólares
                ->where('status', 'completada')
                ->pluck('invoice_number');

            $cashSalesBs = 0;
            if ($salesBs->isNotEmpty()) {
                $items = \App\Models\SaleItem::whereIn('sale_id', $salesBs)->get();
                $itemsTotal = $items->sum(function($item) {
                    return $item->price * $item->quantity;
                });
                $services = \App\Models\SaleService::whereIn('sale_id', $salesBs)->get();
                $servicesTotal = $services->sum('price');
                $cashSalesBs = $itemsTotal + $servicesTotal;
            }

            // Ventas en Dólares (payment_method_id = 1)
            $salesUsd = Sale::where('created_at', '>=', $cashRegister->created_at)
                ->where('payment_method_id', 1) // Dólares
                ->where('status', 'completada')
                ->pluck('invoice_number');

            $cashSalesUsd = 0;
            if ($salesUsd->isNotEmpty()) {
                $items = \App\Models\SaleItem::whereIn('sale_id', $salesUsd)->get();
                $itemsTotal = $items->sum(function($item) {
                    return $item->price * $item->quantity;
                });
                $services = \App\Models\SaleService::whereIn('sale_id', $salesUsd)->get();
                $servicesTotal = $services->sum('price');
                $cashSalesUsd = $itemsTotal + $servicesTotal;
            }

            // Actualizar la caja
            $cashRegister->update([
                'final_amount_moneda1' => $request->final_amount_bs,
                'final_amount_moneda2' => $request->final_amount_usd,
                'cash_sales_moneda1' => $cashSalesBs,
                'cash_sales_moneda2' => $cashSalesUsd,
                'status' => 'cerrada',
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('cash.index')
                ->with('success', 'Caja cerrada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cash.index')
                ->with('error', 'Error al cerrar la caja: ' . $e->getMessage());
        }
    }

    public function getCashRegisters()
    {
        $cashRegisters = CashRegister::with(['user', 'exchangeRate'])
            ->where('status', 'cerrada')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($cashRegisters);
    }

    public function getTodayCashSales()
    {
        $openCashRegister = CashRegister::getOpenCashRegister();
        
        if (!$openCashRegister) {
            return response()->json(['cash_sales_bs' => 0, 'cash_sales_usd' => 0]);
        }

        // Calcular ventas desde la apertura de la caja actual
        // Ventas en Bolívares (todos los métodos EXCEPTO Dólares)
        $salesBs = Sale::where('created_at', '>=', $openCashRegister->created_at)
            ->where('payment_method_id', '!=', 1) // Todos excepto Dólares
            ->where('status', 'completada')
            ->pluck('invoice_number');

        $cashSalesBs = 0;
        if ($salesBs->isNotEmpty()) {
            $items = \App\Models\SaleItem::whereIn('sale_id', $salesBs)->get();
            $itemsTotal = $items->sum(function($item) {
                return $item->price * $item->quantity;
            });
            $services = \App\Models\SaleService::whereIn('sale_id', $salesBs)->get();
            $servicesTotal = $services->sum('price');
            $cashSalesBs = $itemsTotal + $servicesTotal;
        }

        // Ventas en Dólares (payment_method_id = 1)
        $salesUsd = Sale::where('created_at', '>=', $openCashRegister->created_at)
            ->where('payment_method_id', 1) // Dólares
            ->where('status', 'completada')
            ->pluck('invoice_number');

        $cashSalesUsd = 0;
        if ($salesUsd->isNotEmpty()) {
            $items = \App\Models\SaleItem::whereIn('sale_id', $salesUsd)->get();
            $itemsTotal = $items->sum(function($item) {
                return $item->price * $item->quantity;
            });
            $services = \App\Models\SaleService::whereIn('sale_id', $salesUsd)->get();
            $servicesTotal = $services->sum('price');
            $cashSalesUsd = $itemsTotal + $servicesTotal;
        }

        return response()->json([
            'cash_sales_bs' => $cashSalesBs,
            'cash_sales_usd' => $cashSalesUsd
        ]);
    }
}