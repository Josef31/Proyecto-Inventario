<?php

namespace App\Http\Controllers;

use App\Models\Consumption;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConsumptionController extends Controller
{
    /**
     * Display a listing of consumptions
     */
    public function index()
    {
        $consumptions = Consumption::with(['product', 'user'])
            ->orderBy('consumption_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $products = Product::orderBy('name')->get();
        
        return view('consumption.index', compact('consumptions', 'products'));
    }

    /**
     * Store a newly created consumption
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'consumption_date' => 'required|date|before_or_equal:today',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que hay suficiente stock
            $product = Product::findOrFail($validated['product_id']);
            
            if ($product->stock_initial < $validated['quantity']) {
                return redirect()->back()
                    ->withErrors(['quantity' => 'No hay suficiente stock. Stock actual: ' . $product->stock_initial])
                    ->withInput();
            }

            // Crear el consumo
            Consumption::create([
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::id(),
                'consumption_date' => $validated['consumption_date'],
            ]);

            // Reducir el stock del producto
            $product->stock_initial -= $validated['quantity'];
            $product->save();

            DB::commit();
            return redirect()->route('consumption.index')->with('success', '¡Consumo registrado exitosamente! Stock actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar el consumo: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified consumption
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $consumption = Consumption::findOrFail($id);
            
            // Restaurar el stock del producto
            $product = Product::findOrFail($consumption->product_id);
            $product->stock_initial += $consumption->quantity;
            $product->save();

            // Eliminar el consumo
            $consumption->delete();

            DB::commit();
            return redirect()->route('consumption.index')->with('success', '¡Consumo eliminado y stock restaurado!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar el consumo: ' . $e->getMessage());
        }
    }
}
