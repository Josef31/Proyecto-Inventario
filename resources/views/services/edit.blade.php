@extends('layouts.app')

@section('title', 'Editar Servicio | Sistema de Administración')

@section('content')
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil">
                <img class="icono" src="{{ asset('images/perfil.png') }}" alt="Usuario" width="80" height="80">
            </div>
            <p class="nombre-usuario">{{ auth()->user()->name }}</p>
            <p class="rol-usuario">Administrador</p>
        </div>

        <div class="seccion-acciones-admin">
            <h3>ACCIONES</h3>
            <a href="{{ route('services.index') }}" class="btn-admin-accion">Volver a Servicios</a>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Editar Servicio: <strong>{{ $service->name }}</strong></h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="seccion-formulario" style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <form action="{{ route('services.update', $service->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div style="margin-bottom: 20px;">
                    <label for="nombre-servicio" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Nombre del Servicio:</label>
                    <input type="text" id="nombre-servicio" name="name" value="{{ old('name', $service->name) }}" 
                           placeholder="Ej: Formateo e Instalación de Windows" required
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 1em;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="descripcion-servicio" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Descripción:</label>
                    <textarea id="descripcion-servicio" name="description" 
                              placeholder="Descripción detallada del servicio..."
                              style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 1em; min-height: 100px; resize: vertical;">{{ old('description', $service->description) }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label for="costo-base" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Costo Base ($):</label>
                        <input type="number" id="costo-base" name="base_cost" 
                               value="{{ old('base_cost', $service->base_cost) }}" min="0" step="0.01" 
                               placeholder="0.00" required
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 1em;">
                    </div>
                    
                    <div>
                        <label for="tarifa-cliente" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Tarifa al Cliente ($):</label>
                        <input type="number" id="tarifa-cliente" name="customer_rate" 
                               value="{{ old('customer_rate', $service->customer_rate) }}" min="0" step="0.01" 
                               placeholder="0.00" required
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 1em;">
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <label for="duracion-estimada" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Duración Estimada (minutos):</label>
                    <input type="number" id="duracion-estimada" name="estimated_duration" 
                           value="{{ old('estimated_duration', $service->estimated_duration) }}" min="1" 
                           placeholder="60" required
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 1em;">
                </div>

                <!-- Información de cálculo automático -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 25px; border-left: 4px solid #3498db;">
                    <h4 style="margin-bottom: 15px; color: #2c3e50;">Información del Servicio</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 0.9em;">
                        <div>
                            <strong>Ganancia Estimada:</strong><br>
                            <span style="color: #27ae60; font-weight: bold;">
                                ${{ number_format($service->estimated_profit, 2) }}
                            </span>
                        </div>
                        <div>
                            <strong>Margen de Ganancia:</strong><br>
                            <span style="color: #e67e22; font-weight: bold;">
                                {{ number_format($service->profit_margin, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <a href="{{ route('services.index') }}" 
                       style="flex: 1; padding: 12px; background-color: #95a5a6; color: white; text-align: center; border-radius: 4px; font-weight: bold; text-decoration: none;">
                        Cancelar
                    </a>
                    <button type="submit" 
                            style="flex: 1; padding: 12px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1em;">
                        Actualizar Servicio
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script>
// Validación en tiempo real para asegurar que la tarifa sea mayor al costo base
document.addEventListener('DOMContentLoaded', function() {
    const costoBase = document.getElementById('costo-base');
    const tarifaCliente = document.getElementById('tarifa-cliente');
    
    function validarTarifa() {
        const costo = parseFloat(costoBase.value) || 0;
        const tarifa = parseFloat(tarifaCliente.value) || 0;
        
        if (tarifa <= costo && tarifa > 0) {
            tarifaCliente.style.borderColor = '#e74c3c';
            tarifaCliente.style.backgroundColor = '#fdf2f2';
        } else {
            tarifaCliente.style.borderColor = '#ddd';
            tarifaCliente.style.backgroundColor = '';
        }
    }
    
    costoBase.addEventListener('input', validarTarifa);
    tarifaCliente.addEventListener('input', validarTarifa);
    
    // Validar el formulario antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const costo = parseFloat(costoBase.value) || 0;
        const tarifa = parseFloat(tarifaCliente.value) || 0;
        
        if (tarifa <= costo) {
            e.preventDefault();
            alert('La tarifa al cliente debe ser mayor al costo base.');
            tarifaCliente.focus();
        }
    });
});
</script>
@endsection