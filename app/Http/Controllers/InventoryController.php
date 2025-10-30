<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        // Obtener productos de la base de datos
        $products = Product::all();
        
        // Calcular total invertido
        $totalInvested = Product::get()->sum('total_investment');

        // Pasar los datos del usuario autenticado a la vista
        $user = [
            'name' => Auth::user()->name,
            'role' => 'Administrador'
        ];

        return view('inventory.index', compact('user', 'products', 'totalInvested'));
    }

    public function store(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classification' => 'required|string|max:100',
            'price_buy' => 'required|numeric|min:0',
            'price_sell' => 'required|numeric|min:0',
            'stock_initial' => 'required|integer|min:0',
            'stock_minimum' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        // Validar margen mínimo del 30%
        $minSellPrice = $validated['price_buy'] * 1.3;
        if ($validated['price_sell'] < $minSellPrice) {
            return response()->json([
                'success' => false,
                'error' => 'El precio de venta no cumple con el margen mínimo del 30% requerido.'
            ], 422);
        }

        try {
            // Crear el producto
            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'product' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getInventoryStats()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_investment' => Product::get()->sum('total_investment'),
            'low_stock_count' => Product::lowStock()->count(),
            'out_of_stock_count' => Product::outOfStock()->count(),
            'near_expiration_count' => Product::nearExpiration()->count(),
        ];

        return response()->json($stats);
    }
}