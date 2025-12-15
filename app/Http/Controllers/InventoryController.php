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
            'expiration_date' => 'nullable|date|after_or_equal:today',
        ]);

        $minSellPrice = $validated['price_buy'] * 1.3;
        if ($validated['price_sell'] < $minSellPrice) {
            return redirect()->back()->withErrors(['price_sell' => 'El precio de venta no cumple con el margen mínimo del 30% requerido.'])->withInput();
        }

        // Validar fecha de vencimiento y mostrar advertencia si está próxima
        if (isset($validated['expiration_date'])) {
            $expirationDate = \Carbon\Carbon::parse($validated['expiration_date']);
            $daysUntilExpiration = now()->diffInDays($expirationDate, false);
            
            if ($daysUntilExpiration < 30 && $daysUntilExpiration >= 0) {
                session()->flash('warning', "⚠️ Advertencia: Este producto vence en {$daysUntilExpiration} días.");
            }
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
            'expiration_date' => 'nullable|date|after_or_equal:today',
        ]);
        
        $minSellPrice = $validated['price_buy'] * 1.3;
        if ($validated['price_sell'] < $minSellPrice) {
            return redirect()->back()->withErrors(['price_sell' => 'El precio de venta no cumple con el margen mínimo del 30% requerido.'])->withInput();
        }

        // Validar fecha de vencimiento y mostrar advertencia si está próxima
        if (isset($validated['expiration_date'])) {
            $expirationDate = \Carbon\Carbon::parse($validated['expiration_date']);
            $daysUntilExpiration = now()->diffInDays($expirationDate, false);
            
            if ($daysUntilExpiration < 30 && $daysUntilExpiration >= 0) {
                session()->flash('warning', "⚠️ Advertencia: Este producto vence en {$daysUntilExpiration} días.");
            }
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

    public function importProducts(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            if (count($rows) < 2) {
                return response()->json(['success' => false, 'message' => 'El archivo está vacío'], 400);
            }
            $headers = array_map('trim', $rows[0]);
            $requiredHeaders = ['name', 'id_classification', 'price_buy', 'price_sell', 'stock_initial', 'stock_minimum'];
            $missingHeaders = array_diff($requiredHeaders, $headers);
            if (!empty($missingHeaders)) {
                return response()->json(['success' => false, 'message' => 'Faltan columnas: ' . implode(', ', $missingHeaders)], 400);
            }
            $columnMap = array_flip($headers);
            $imported = 0;
            $errors = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;
                try {
                    $name = trim($row[$columnMap['name']] ?? '');
                    $idClassification = (int)($row[$columnMap['id_classification']] ?? 0);
                    $priceBuy = (float)($row[$columnMap['price_buy']] ?? 0);
                    $priceSell = (float)($row[$columnMap['price_sell']] ?? 0);
                    $stockInitial = (int)($row[$columnMap['stock_initial']] ?? 0);
                    $stockMinimum = (int)($row[$columnMap['stock_minimum']] ?? 0);
                    $expirationDate = isset($columnMap['expiration_date']) ? $row[$columnMap['expiration_date']] : null;
                    if (empty($name) || $idClassification < 1 || $idClassification > 5 || $priceBuy <= 0 || $priceSell <= 0) {
                        $errors[] = "Fila " . ($i + 1) . ": Datos inválidos";
                        continue;
                    }
                    if ($priceSell < ($priceBuy * 1.3)) {
                        $errors[] = "Fila " . ($i + 1) . ": Precio de venta no cumple margen del 30%";
                        continue;
                    }
                    $formattedDate = null;
                    if ($expirationDate && strtoupper(trim($expirationDate)) !== 'N/A') {
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', trim($expirationDate), $matches)) {
                            $formattedDate = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
                        }
                    }
                    Product::create(['name' => $name, 'id_classification' => $idClassification, 'price_buy' => $priceBuy, 'price_sell' => $priceSell, 'stock_initial' => $stockInitial, 'stock_minimum' => $stockMinimum, 'expiration_date' => $formattedDate]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Fila " . ($i + 1) . ": " . $e->getMessage();
                }
            }
            return response()->json(['success' => true, 'imported' => $imported, 'errors' => $errors]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    public function getHistory($id)
    {
        try {
            $product = Product::with([
                'purchaseItems.purchase.supplier', // Asumiendo relaciones nested
                'saleItems.sale.customer',
                'consumptions.user'
            ])->findOrFail($id);

            $movements = collect();

            // 1. Compras (Entradas)
            foreach ($product->purchaseItems as $item) {
                if ($item->purchase) {
                    $movements->push([
                        'date' => $item->purchase->purchase_date, // Asumiendo campo fecha
                        'type' => 'Compra',
                        'quantity' => $item->quantity,
                        'price' => $item->unit_cost,
                        'total' => $item->line_total,
                        'reference' => 'Factura: ' . ($item->purchase->invoice_number ?? 'N/A'),
                        'detail' => $item->purchase->supplier ? $item->purchase->supplier->name : 'Proveedor General',
                        'user' => $item->purchase->user_id // Podrías cargar el usuario también
                    ]);
                }
            }

            // 2. Ventas (Salidas)
            foreach ($product->saleItems as $item) {
                if ($item->sale) {
                    $movements->push([
                        'date' => $item->sale->created_at->format('Y-m-d'), // Asumiendo timestamp
                        'type' => 'Venta',
                        'quantity' => $item->quantity * -1, // Negativo para salida
                        'price' => $item->price,
                        'total' => $item->price * $item->quantity,
                        'reference' => 'Ticket: ' . $item->sale->sale_code,
                        'detail' => $item->sale->customer ? $item->sale->customer->name : 'Cliente General',
                        'user' => $item->sale->user_id
                    ]);
                }
            }

            // 3. Consumos (Salidas)
            foreach ($product->consumptions as $consumption) {
                $movements->push([
                    'date' => \Carbon\Carbon::parse($consumption->consumption_date)->format('Y-m-d'),
                    'type' => 'Consumo',
                    'quantity' => $consumption->quantity * -1, // Negativo para salida
                    'price' => 0, // Consumo interno no suele tener precio de venta
                    'total' => 0,
                    'reference' => 'Interno',
                    'detail' => $consumption->reason . ($consumption->notes ? ' (' . $consumption->notes . ')' : ''),
                    'user' => $consumption->user ? $consumption->user->name : 'N/A'
                ]);
            }

            // Ordenar por fecha descendente
            $sortedMovements = $movements->sortByDesc('date')->values();

            return response()->json([
                'success' => true,
                'product' => $product->name,
                'movements' => $sortedMovements
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener historial: ' . $e->getMessage()], 500);
        }
    }
}
