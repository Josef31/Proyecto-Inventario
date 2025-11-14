<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class InventoryController extends Controller
{
    // Mostrar vista de inventario / ventas
    public function index()
    {
        return view('inventory.index');
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
    public function storeSale(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_rfc' => 'nullable|string|max:50',
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
            'items' => 'required|array',
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['id']);

            if (!$product) {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }

            if ($product->stock_initial < $item['quantity']) {
                return response()->json([
                    'error' => 'Stock insuficiente para: ' . $product->name
                ], 400);
            }

            // Restar stock
            $product->stock_initial -= $item['quantity'];
            $product->save();
        }

        return response()->json([
            'message' => 'Venta registrada correctamente'
        ]);
    }
}
