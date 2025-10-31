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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'customer_rate' => 'required|numeric|min:0',
            'estimated_duration' => 'required|integer|min:1'
        ]);

        try {
            BusinessService::create([
                'name' => $request->name,
                'description' => $request->description,
                'base_cost' => $request->base_cost,
                'customer_rate' => $request->customer_rate,
                'estimated_duration' => $request->estimated_duration
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el servicio: ' . $e->getMessage()
            ], 500);
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

        try {
            $service = BusinessService::findOrFail($id);
            $service->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $service = BusinessService::findOrFail($id);
            $service->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getServices()
    {
        $services = BusinessService::active()->get();
        
        return response()->json($services);
    }
}