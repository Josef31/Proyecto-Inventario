<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('id', 'desc')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'rfc' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        try {
            // Si se proporcionó RFC, verificar si ya existe
            if (!empty($validated['rfc'])) {
                $existingSupplier = Supplier::where('rfc', $validated['rfc'])->first();
                
                if ($existingSupplier) {
                    // Si existe y está activo, mostrar advertencia
                    if ($existingSupplier->is_active) {
                        return redirect()->back()
                            ->with('error', '⚠️ Ya existe un proveedor activo con el RFC: ' . $validated['rfc'])
                            ->withInput();
                    }
                    
                    // Si existe pero está inactivo, solo reactivarlo (sin modificar datos)
                    $existingSupplier->update(['is_active' => true]);
                    
                    return redirect()->route('suppliers.index')
                        ->with('success', '✅ Proveedor reactivado exitosamente: ' . $existingSupplier->name);
                }
            }
            
            // Si no existe, crear nuevo proveedor
            Supplier::create($validated);
            return redirect()->route('suppliers.index')->with('success', '¡Proveedor creado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el proveedor: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'rfc' => 'required|string|max:255|unique:suppliers,rfc,' . $id,
            'phone' => 'required|string|max:20',
        ]);

        try {
            $supplier->update($validated);
            return redirect()->route('suppliers.index')->with('success', '¡Proveedor actualizado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el proveedor: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified supplier
     */
    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            \Illuminate\Support\Facades\Log::info('Desactivando proveedor: ' . $supplier->id);
            $supplier->update(['is_active' => false]);
            return redirect()->route('suppliers.index')->with('success', '¡Proveedor desactivado exitosamente! (Soft Delete)');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al desactivar el proveedor: ' . $e->getMessage());
        }
    }
}
