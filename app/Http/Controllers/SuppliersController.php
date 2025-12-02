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
        $suppliers = Supplier::orderBy('id', 'desc')->get();
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
        ]);

        try {
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
            $supplier->delete();
            return redirect()->route('suppliers.index')->with('success', '¡Proveedor eliminado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el proveedor: ' . $e->getMessage());
        }
    }
}
