@extends('layouts.app')

@section('title', 'Detalle de Factura de Compra | Sistema de Administración')

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
            <h3>INFORMACIÓN DE FACTURA</h3>
            
            <div style="margin-bottom: 15px; font-size: 0.9em;">
                <p><strong>Tipo:</strong> <span style="color: #3498db;">COMPRA</span></p>
                <p><strong>Factura:</strong> {{ $purchase->invoice_number }}</p>
                <p><strong>Fecha:</strong> {{ $purchase->purchase_date->format('d/m/Y') }}</p>
                <p><strong>Proveedor:</strong> {{ $purchase->supplier->name }}</p>
                <p><strong>Usuario:</strong> {{ $purchase->user->name }}</p>
                
                @if($purchase->notes)
                    <hr style="margin: 10px 0; border: none; border-top: 1px solid #4a6681;">
                    <p><strong>Notas:</strong></p>
                    <p style="font-size: 0.85em;">{{ $purchase->notes }}</p>
                @endif
            </div>
            
            <a href="{{ route('invoices.index') }}" class="btn-listo" style="display: inline-block; text-align: center;">
                Volver a Facturas
            </a>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Factura de Compra: {{ $purchase->invoice_number }}</h2>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Costo Unitario</th>
                    <th>Total Línea</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>${{ number_format($item->unit_cost, 2) }}</td>
                        <td>${{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #3498db; color: white; font-weight: bold; font-size: 1.1em;">
                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                    <td>${{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </main>
</div>
@endsection
