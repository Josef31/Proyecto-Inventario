@extends('layouts.app')

@section('title', 'Servicios | Sistema de Administración')

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

        <div class="seccion-formulario">
            <h3>NUEVO SERVICIO</h3>

            <form action="{{ route('services.store') }}" method="POST">
                @csrf
                <label for="nombre-servicio">Nombre del Servicio:</label>
                <input type="text" id="nombre-servicio" name="name" placeholder="Ej: Cambio de aceite" required>

                <label for="descripcion-servicio">Descripción:</label>
                <textarea id="descripcion-servicio" name="description" placeholder="Descripción del servicio..."></textarea>

                <label for="costo-base">Costo Base (Materiales/Hora):</label>
                <input type="number" id="costo-base" name="base_cost" min="0" step="0.01" placeholder="0.00" required>

                <label for="tarifa-cliente">Tarifa al Cliente:</label>
                <input type="number" id="tarifa-cliente" name="customer_rate" min="0" step="0.01" placeholder="0.00" required>

                <label for="duracion-estimada">Duración Estimada (minutos):</label>
                <input type="number" id="duracion-estimada" name="estimated_duration" min="1" placeholder="60" required>

                <button type="submit" class="btn-listo">Guardar Servicio</button>
            </form>
        </div>

        <div class="seccion-acciones-admin">
            <h3>REPORTES</h3>
            <button class="btn-admin-accion btn-reporte-servicios" onclick="generarReporte()">Generar Reporte de Servicios</button>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Servicios Disponibles</h2>
            <p class="total-invertido">Total de Servicios: <span id="total-servicios">{{ $totalServices }}</span></p>
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

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre del Servicio</th>
                    <th>Descripción</th>
                    <th>Costo Base</th>
                    <th>Tarifa Cliente</th>
                    <th>Duración (min)</th>
                    <th>Ganancia Estimada</th>
                    <th>Margen %</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo-servicios">
                @foreach($services as $index => $service)
                <tr id="servicio-{{ $service->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $service->name }}</strong></td>
                    <td>{{ $service->description ?? 'Sin descripción' }}</td>
                    <td>${{ number_format($service->base_cost, 2) }}</td>
                    <td>${{ number_format($service->customer_rate, 2) }}</td>
                    <td>{{ $service->estimated_duration }}</td>
                    <td>${{ number_format($service->estimated_profit, 2) }}</td>
                    <td>{{ number_format($service->profit_margin, 1) }}%</td>
                    <td>
                        <!-- Botón Editar como enlace CORREGIDO -->
                        <a href="{{ route('services.edit', $service->id) }}" class="btn-editar">Editar</a>

                        <!-- Botón Eliminar -->
                        <form action="{{ route('services.destroy', $service->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-eliminar" onclick="return confirm('¿Está seguro de que desea eliminar este servicio?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>
@endsection

@section('scripts')
<script>
    function generarReporte() {
        alert('Generando reporte de servicios...');
    }
</script>
@endsection