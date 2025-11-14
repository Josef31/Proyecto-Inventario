<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashController extends Controller
{
    public function index()
    {
        $openCashRegister = CashRegister::getOpenCashRegister();
        $closedCashRegisters = CashRegister::closed()
            ->with('user')
            ->orderBy('closed_at', 'desc')
            ->limit(50)
            ->get();
        
        $totalCortes = $closedCashRegisters->count();

        // Calcular ventas en efectivo del día si hay caja abierta
        $cashSalesToday = 0;
        if ($openCashRegister) {
            $cashSalesToday = Sale::whereDate('created_at', today())
                ->where('payment_method', 'efectivo')
                ->where('status', 'completada')
                ->sum('total');
        }

        return view('cash.index', compact(
            'openCashRegister', 
            'closedCashRegisters', 
            'totalCortes',
            'cashSalesToday'
        ));
    }

    public function openCashRegister(Request $request)
    {
        $request->validate([
            'initial_amount' => 'required|numeric|min:0'
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
                'initial_amount' => $request->initial_amount,
                'status' => 'abierta',
                'opened_at' => now()
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
            'final_amount' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $cashRegister = CashRegister::getOpenCashRegister();
            
            if (!$cashRegister) {
                return redirect()->route('cash.index')
                    ->with('error', 'No hay caja abierta para cerrar');
            }

            // Calcular ventas en efectivo desde la apertura de la caja
            $cashSales = Sale::where('created_at', '>=', $cashRegister->opened_at)
                ->where('payment_method', 'efectivo')
                ->where('status', 'completada')
                ->sum('total');

            $expectedAmount = $cashRegister->initial_amount + $cashSales;
            $difference = $request->final_amount - $expectedAmount;

            // Actualizar la caja
            $cashRegister->update([
                'final_amount' => $request->final_amount,
                'cash_sales' => $cashSales,
                'expected_amount' => $expectedAmount,
                'difference' => $difference,
                'status' => 'cerrada',
                'closed_at' => now()
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
        $cashRegisters = CashRegister::with('user')
            ->closed()
            ->orderBy('closed_at', 'desc')
            ->get();

        return response()->json($cashRegisters);
    }

    public function getTodayCashSales()
    {
        $openCashRegister = CashRegister::getOpenCashRegister();
        
        if (!$openCashRegister) {
            return response()->json(['cash_sales' => 0]);
        }

        // Calcular ventas desde la apertura de la caja actual
        $cashSales = Sale::where('created_at', '>=', $openCashRegister->opened_at)
            ->where('payment_method', 'efectivo')
            ->where('status', 'completada')
            ->sum('total');

        return response()->json(['cash_sales' => $cashSales]);
    }
}