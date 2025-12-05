<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchasesController extends Controller
{
    /**
     * Display a listing of purchases
     */
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->orderBy('purchase_date', 'desc')
            ->get();
        
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('purchases.index', compact('purchases', 'suppliers', 'products'));
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_suppliers' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255|unique:purchases,invoice_number',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Crear la compra
            $purchase = Purchase::create([
                'id_suppliers' => $validated['id_suppliers'],
                'purchase_date' => $validated['purchase_date'],
                'invoice_number' => $validated['invoice_number'],
                'user_id' => Auth::id(),
                'total_amount' => $validated['total_amount'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Crear los items y actualizar stock
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_cost'];
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $lineTotal,
                ]);

                // Incrementar el stock del producto
                $product = Product::find($item['product_id']);
                $product->stock_initial += $item['quantity'];
                $product->save();
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', '¡Compra registrada exitosamente! Stock actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar la compra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified purchase
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'user', 'items.product'])->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Remove the specified purchase
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::with('items')->findOrFail($id);

            // Revertir el stock antes de eliminar
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                $product->stock_initial -= $item->quantity;
                $product->save();
            }

            // Eliminar items y compra
            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();
            return redirect()->route('purchases.index')->with('success', '¡Compra eliminada exitosamente! Stock revertido.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Search products for purchase
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('id', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'price_buy', 'stock_initial']);

        return response()->json($products);
    }
}
