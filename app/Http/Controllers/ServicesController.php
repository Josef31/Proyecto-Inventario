<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessService;
use Illuminate\Support\Facades\DB;

class ServicesController extends Controller
{
    public function index()
    {
        $services = BusinessService::active()->get();
        $totalServices = $services->count();

        return view('services.index', compact('services', 'totalServices'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'customer_rate' => 'required|numeric|min:0',
            'estimated_duration' => 'required|integer|min:1'
        ]);

        // Validar que la tarifa sea mayor al costo base
        if ($request->customer_rate <= $request->base_cost) {
            return redirect()->back()->with('error', 'La tarifa al cliente debe ser mayor al costo base.');
        }

        try {
            BusinessService::create([
                'name' => $request->name,
                'description' => $request->description,
                'base_cost' => $request->base_cost,
                'customer_rate' => $request->customer_rate,
                'estimated_duration' => $request->estimated_duration
            ]);

            return redirect()->route('services.index')->with('success', 'Servicio creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el servicio: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $service = BusinessService::findOrFail($id);
            return view('services.edit', compact('service'));
        } catch (\Exception $e) {
            return redirect()->route('services.index')->with('error', 'Servicio no encontrado');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'customer_rate' => 'required|numeric|min:0',
            'estimated_duration' => 'required|integer|min:1'
        ]);

        // Validar que la tarifa sea mayor al costo base
        if ($request->customer_rate <= $request->base_cost) {
            return redirect()->back()->with('error', 'La tarifa al cliente debe ser mayor al costo base.');
        }

        try {
            $service = BusinessService::findOrFail($id);
            $service->update([
                'name' => $request->name,
                'description' => $request->description,
                'base_cost' => $request->base_cost,
                'customer_rate' => $request->customer_rate,
                'estimated_duration' => $request->estimated_duration
            ]);

            return redirect()->route('services.index')->with('success', 'Servicio actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el servicio: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $service = BusinessService::findOrFail($id);
            $service->update(['is_active' => false]);

            return redirect()->route('services.index')->with('success', 'Servicio eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el servicio: ' . $e->getMessage());
        }
    }
}
