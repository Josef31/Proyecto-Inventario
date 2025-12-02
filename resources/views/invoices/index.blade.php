@extends('layouts.app')

@section('title', 'Facturas | Sistema de Administración')

@section('content')
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil">
                <img class="icono" src="{{ asset('images/perfil.png') }}" alt="Usuario" width="80" height="80">
            </div>
            <p class="nombre-usuario">{{ auth()->user()->name }}</p>
            <p class="rol-usuario">{{ auth()->user()->role ? auth()->user()->role->name : 'Administrador' }}</p>
        </div>
        
        <div class="seccion-formulario">
            <h3>Gestión de Facturas</h3>
            <p style="font-size: 0.9em; color: #ecf0f1;">Las facturas se generan automáticamente al registrar ventas y compras con número de factura.</p>
            <hr style="margin: 15px 0; border: none; border-top: 1px solid #4a6681;">
            <p><strong>Total de facturas:</strong> <span style="font-size: 1.2em; color: #2ecc71;">{{ $totalInvoices }}</span></p>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Historial de Facturas</h2>
            <p class="total-invertido">Facturas de ventas y compras registradas en el sistema</p>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th># Factura</th>
                    <th>Fecha</th>
                    <th>Cliente/Proveedor</th>
                    <th>Total</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; {{ $invoice['type'] == 'venta' ? 'background-color: #4CAF50; color: white;' : 'background-color: #2196F3; color: white;' }}">
                            {{ strtoupper($invoice['type']) }}
                        </span>
                    </td>
                    <td>{{ $invoice['invoice_number'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }}</td>
                    <td>{{ $invoice['entity'] }}</td>
                    <td>${{ number_format($invoice['total'], 2) }}</td>
                    <td>{{ $invoice['user'] }}</td>
                    <td class="acciones">
                        <a href="{{ route('invoices.show', $invoice['invoice_number']) }}" class="btn-editar" style="background-color: #2196F3; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none;">
                            Ver Detalle
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>
@endsection