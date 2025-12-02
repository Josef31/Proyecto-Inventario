@extends('layouts.app')

@section('title', 'Detalle de Factura de Venta | Sistema de Administración')

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
                <p><strong>Tipo:</strong> <span style="color: #2ecc71;">VENTA</span></p>
                <p><strong>Factura:</strong> {{ $sale->invoice_number }}</p>
                <p><strong>Fecha:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Cliente:</strong> {{ $sale->customer ? $sale->customer->name : 'Cliente General' }}</p>
                <p><strong>Vendedor:</strong> {{ $sale->user->name }}</p>
                <p><strong>Método de Pago:</strong> {{ $sale->payment_method_id }}</p>
                <p><strong>Moneda:</strong> {{ $sale->payment_currency }}</p>
            </div>
            
            <a href="{{ route('invoices.index') }}" class="btn-listo" style="display: inline-block; text-align: center;">
                Volver a Facturas
            </a>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Factura de Venta: {{ $sale->invoice_number }}</h2>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #ecf0f1; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Subtotal:</td>
                    <td>${{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                <tr style="background-color: #ecf0f1; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Impuestos:</td>
                    <td>${{ number_format($sale->taxes, 2) }}</td>
                </tr>
                <tr style="background-color: #2ecc71; color: white; font-weight: bold; font-size: 1.1em;">
                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                    <td>${{ number_format($sale->total, 2) }}</td>
                </tr>
                @if($sale->payment_method_id == 1 || $sale->payment_method_id == 5)
                <tr style="background-color: #f8f9fa;">
                    <td colspan="3" style="text-align: right;">Monto Recibido:</td>
                    <td>${{ number_format($sale->amount_received, 2) }}</td>
                </tr>
                <tr style="background-color: #f8f9fa;">
                    <td colspan="3" style="text-align: right;">Cambio:</td>
                    <td>${{ number_format($sale->change, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </main>
</div>
@endsection
