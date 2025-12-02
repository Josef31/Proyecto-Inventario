<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;

class ExchangeRatesController extends Controller
{
    public function index()
    {
        $exchangeRates = ExchangeRate::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $latestRate = ExchangeRate::getLatestRate();
        
        return view('exchange_rates.index', compact('exchangeRates', 'latestRate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'base_currency' => 'required|string|size:3',
            'target_currency' => 'required|string|size:3',
            'rate' => 'required|numeric|min:0',
        ]);

        try {
            ExchangeRate::create([
                'date' => $request->date,
                'base_currency' => strtoupper($request->base_currency),
                'target_currency' => strtoupper($request->target_currency),
                'rate' => $request->rate,
            ]);

            return redirect()->route('exchange_rates.index')
                ->with('success', 'Tasa de cambio creada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('exchange_rates.index')
                ->with('error', 'Error al crear tasa de cambio: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        
        return view('exchange_rates.edit', compact('exchangeRate'));
    }

    public function update(Request $request, $id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'base_currency' => 'required|string|size:3',
            'target_currency' => 'required|string|size:3',
            'rate' => 'required|numeric|min:0',
        ]);

        try {
            $exchangeRate->update([
                'date' => $request->date,
                'base_currency' => strtoupper($request->base_currency),
                'target_currency' => strtoupper($request->target_currency),
                'rate' => $request->rate,
            ]);

            return redirect()->route('exchange_rates.index')
                ->with('success', 'Tasa de cambio actualizada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('exchange_rates.edit', $id)
                ->with('error', 'Error al actualizar tasa de cambio: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $exchangeRate = ExchangeRate::findOrFail($id);
            
            // Verificar si está siendo usada en alguna caja
            if ($exchangeRate->cashRegisters()->count() > 0) {
                return redirect()->route('exchange_rates.index')
                    ->with('error', 'No se puede eliminar esta tasa porque está siendo usada en cajas registradoras');
            }

            $exchangeRate->delete();

            return redirect()->route('exchange_rates.index')
                ->with('success', 'Tasa de cambio eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('exchange_rates.index')
                ->with('error', 'Error al eliminar tasa de cambio: ' . $e->getMessage());
        }
    }
}
