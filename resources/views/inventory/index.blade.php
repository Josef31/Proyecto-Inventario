@extends('layouts.app')

@section('title', 'Inventario | Sistema de Administración')

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
            <h3>NUEVO ARTÍCULO</h3>
            
            <form id="form-producto" method="POST" action="{{ route('inventory.store') }}">
                @csrf
                
                <label for="clasificacion">Clasificación / Talla:</label>
                <select id="clasificacion" name="id_classification" required>
                    <option value="">Seleccione una clasificación</option>
                    @foreach($classifications as $classification)
                        <option value="{{ $classification->id }}" {{ old('id_classification') == $classification->id ? 'selected' : '' }}>
                            {{ $classification->name }}
                        </option>
                    @endforeach
                </select>

                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="name" value="{{ old('name') }}" required>
                
                <label for="precio-compra">Precio Compra:</label>
                <input type="number" id="precio-compra" name="price_buy" min="0" step="0.01" value="{{ old('price_buy') }}" required>
                
                <label for="precio-venta">Precio Venta (Mínimo requerido):</label>
                <input type="number" id="precio-venta" name="price_sell" min="0" step="0.01" value="{{ old('price_sell') }}" required> 
                
                <label for="stock-actual">Stock Inicial:</label>
                <input type="number" id="stock-actual" name="stock_initial" min="0" value="{{ old('stock_initial') }}" required>
                
                <label for="stock-minimo">Stock Mínimo (Alerta):</label>
                <input type="number" id="stock-minimo" name="stock_minimum" min="0" value="{{ old('stock_minimum') }}" required>

                <label for="fecha-vencimiento">Fecha de Vencimiento:</label>
                <input type="date" id="fecha-vencimiento" name="expiration_date" value="{{ old('expiration_date') }}">
                
                <button type="submit" class="btn-listo">Listo</button>
            </form>
        </div>

        {{-- 🚨 CORRECCIÓN: Estilo para centrar el botón --}}
        <div class="seccion-acciones-admin" style="text-align: center;"> 
            <h3>ACCIONES ADMIN</h3>
            {{-- Botón Ajuste de Inventario (Placeholder) --}}
            <a href="{{ route('inventory.adjustment.form') }}" class="btn-admin-accion btn-ajuste-stock">Ajuste de Inventario</a>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Articulos en el Inventario</h2>
            
            {{-- Mensajes de Sesión --}}
            @if (session('success'))
                <div class="alerta-exito" style="display: flex;">
                    <span class="icono-ayuda">✓</span>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error') || session('info'))
                <div class="alerta-error" style="display: flex; background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                    <span class="icono-ayuda">✗</span>
                    <p>{{ session('error') ?? session('info') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="alerta-error" style="display: flex; background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                    <span class="icono-ayuda">✗</span>
                    <p>Error de validación: {{ $errors->first() }}</p>
                </div>
            @endif
            
            <p class="total-invertido">Total invertido en inventario: <span id="total-invertido">${{ number_format($totalInvested, 2) }}</span></p> 
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Clasif.</th>
                    <th>Existencias</th>
                    <th>Mínimo</th>
                    <th>Vencimiento</th>
                    <th>Precio Venta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo">
                @foreach($products as $product)
                    @php
                        // Asumiendo que estos Accessors existen en Product.php
                        $stockClass = $product->getStockClassAttribute();
                        $rowClass = $product->getRowClassAttribute();
                        $vencimientoClass = $product->getIsNearExpirationAttribute() ? 'vencimiento-cerca' : '';
                        $fechaTexto = $product->getFormattedExpirationDateAttribute();
                    @endphp
                    
                    <tr class="{{ $rowClass }}">
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->classification_name }}</td>
                        <td><span class="{{ $stockClass }}">{{ $product->stock_initial }}</span></td>
                        <td>{{ $product->stock_minimum }}</td>
                        <td><span class="{{ $vencimientoClass }}">{{ $fechaTexto }}</span></td>
                        <td>${{ number_format($product->price_sell, 2) }}</td>
                        <td class="acciones">
                            {{-- 🚨 Botón Detalles: Placeholder (sin acción de edición) --}}
                            <button class="btn-detalles" onclick="alert('Mostrando detalles de {{ $product->name }}.')">Detalles</button> 
                            
                            {{-- 🚨 Botón Editar: Dirige a la edición individual --}}
                            <a href="{{ route('inventory.edit', $product->id) }}" class="btn-editar" style="background-color: #039438; color: white; padding: 5px 10px; border-radius: 3px; margin-right: 5px; text-decoration: none;">Editar</a>
                            
                            {{-- Botón Eliminar --}}
                            <form action="{{ route('inventory.destroy', $product->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este artículo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-eliminar">Eliminar</button>
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
    // Cálculo local del precio de venta mínimo (UX)
    document.addEventListener('DOMContentLoaded', function() {
        const precioCompraInput = document.getElementById('precio-compra');
        const precioVentaInput = document.getElementById('precio-venta');
        
        if (precioCompraInput && precioVentaInput) {
            precioCompraInput.addEventListener('input', function() {
                const precioCompra = parseFloat(this.value);
                
                if (isNaN(precioCompra) || precioCompra <= 0) {
                    precioVentaInput.value = '';
                    return;
                }
                
                const precioVentaMinimo = precioCompra * 1.3;
                precioVentaInput.value = precioVentaMinimo.toFixed(2);
            });
        }
    });
</script>
@endsection