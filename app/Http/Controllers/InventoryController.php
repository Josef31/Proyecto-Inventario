<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\ProductClassification;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('classification')->get();
        $classifications = ProductClassification::all();
        $totalInvested = $products->sum('total_investment');
        return view('inventory.index', compact('products', 'classifications', 'totalInvested'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id_classification' => 'required|integer|exists:products_classification,id',
            'price_buy' => 'required|numeric|min:0',
            'price_sell' => 'required|numeric|min:0',
            'stock_initial' => 'required|integer|min:0',
            'stock_minimum' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        $minSellPrice = $validated['price_buy'] * 1.3;
        if ($validated['price_sell'] < $minSellPrice) {
            return redirect()->back()->withErrors(['price_sell' => 'El precio de venta no cumple con el margen mínimo del 30% requerido.'])->withInput();
        }

        try {
            Product::create($validated);
            return redirect()->route('inventory.index')->with('success', '¡Producto creado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el producto: ' . $e->getMessage())->withInput();
        }
    }
    
    // Muestra el formulario de edición individual
    public function edit($id) 
    {
        $product = Product::with('classification')->findOrFail($id);
        $classifications = ProductClassification::all();
        return view('inventory.edit', compact('product', 'classifications'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id_classification' => 'required|integer|exists:products_classification,id',
            'price_buy' => 'required|numeric|min:0',
            'price_sell' => 'required|numeric|min:0',
            'stock_initial' => 'required|integer|min:0',
            'stock_minimum' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);
        
        $minSellPrice = $validated['price_buy'] * 1.3;
        if ($validated['price_sell'] < $minSellPrice) {
            return redirect()->back()->withErrors(['price_sell' => 'El precio de venta no cumple con el margen mínimo del 30% requerido.'])->withInput();
        }

        try {
            $product->update($validated);
            return redirect()->route('inventory.index')->with('success', '¡Producto actualizado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el producto: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return redirect()->route('inventory.index')->with('success', '¡Producto eliminado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    public function showAdjustmentForm()
    {
        // En este punto, solo redirigimos a index con un mensaje o a una nueva vista
        return redirect()->route('inventory.index')->with('info', 'Funcionalidad de Ajuste de Inventario Masivo: En desarrollo.');
    }

}