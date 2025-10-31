<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashController extends Controller
{
    public function index()
    {
        $openCashRegister = CashRegister::getOpenCashRegister();
        $closedCashRegisters = CashRegister::closed()
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
            return response()->json([
                'success' => false,
                'message' => 'Ya hay una caja abierta'
            ], 422);
        }

        try {
            $cashRegister = CashRegister::create([
                'user_id' => Auth::id(),
                'initial_amount' => $request->initial_amount,
                'status' => 'abierta',
                'opened_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Caja abierta exitosamente',
                'cash_register' => $cashRegister
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al abrir la caja: ' . $e->getMessage()
            ], 500);
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
                return response()->json([
                    'success' => false,
                    'message' => 'No hay caja abierta'
                ], 422);
            }

            // Calcular ventas en efectivo del día
            $cashSales = Sale::whereDate('created_at', today())
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

            return response()->json([
                'success' => true,
                'message' => 'Caja cerrada exitosamente',
                'cash_register' => $cashRegister
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar la caja: ' . $e->getMessage()
            ], 500);
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
        $cashSales = Sale::whereDate('created_at', today())
            ->where('payment_method', 'efectivo')
            ->where('status', 'completada')
            ->sum('total');

        return response()->json(['cash_sales' => $cashSales]);
    }
}