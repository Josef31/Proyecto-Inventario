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
}
