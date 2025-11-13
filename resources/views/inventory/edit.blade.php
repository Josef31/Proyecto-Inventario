@extends('layouts.app')

@section('title', 'Editar Artículo | ' . $product->name)

@section('content')

{{-- 1. Contenedor principal con barra lateral --}}
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
            <a href="{{ route('inventory.index') }}" class="btn-admin-accion">Volver a Inventario</a>
        </div>
    </aside>

    {{-- 2. Contenedor del formulario --}}
    <div style="
        flex-grow: 1;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px 20px;
        background-color: #f4f7f9;
        min-height: 90vh;
        font-family: Arial, sans-serif;
    ">
        {{-- 3. Tarjeta blanca flotante --}}
        <div style="
            width: 100%;
            max-width: 800px; 
            background-color: #ffffff;
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 30px;
        ">
            <h2 style="
                color: #333;
                margin-bottom: 25px;
                font-size: 1.6em;
                font-weight: 600;
                padding-bottom: 15px;
                border-bottom: 1px solid #e0e0e0;
            ">
                Editar Artículo: **{{ $product->name }}**
            </h2>
            
            {{-- Mensajes de Sesión y Errores --}}
            @if (session('error'))
                <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0;">{{ session('error') }}</p>
                </div>
            @endif
            
            @if ($errors->any())
                <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0;">Por favor, corrige los siguientes errores de validación:</p>
                    <ul style="padding-left: 20px; margin-top: 5px; margin-bottom: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{-- Fin de Mensajes --}}

            <form method="POST" action="{{ route('inventory.update', $product->id) }}">
                @csrf
                @method('PUT') 

                {{-- Grid de 2 columnas --}}
                <div style="
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 20px 30px;
                    margin-bottom: 30px;
                ">
                    
                    {{-- Nombre --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="nombre" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Nombre del Artículo:</label>
                        <input type="text" id="nombre" name="name" value="{{ old('name', $product->name) }}" required 
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>

                    {{-- Clasificación --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="clasificacion" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Clasificación / Talla:</label>
                        <select id="clasificacion" name="classification"
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                            <option value="N/A" {{ old('classification', $product->classification) == 'N/A' ? 'selected' : '' }}>N/A</option>
                            <option value="Calzado" {{ old('classification', $product->classification) == 'Calzado' ? 'selected' : '' }}>Calzado</option>
                            <option value="Vestido" {{ old('classification', $product->classification) == 'Vestido' ? 'selected' : '' }}>Vestido</option>
                            <option value="S" {{ old('classification', $product->classification) == 'S' ? 'selected' : '' }}>Talla S</option>
                            <option value="Papelería" {{ old('classification', $product->classification) == 'Papelería' ? 'selected' : '' }}>Papelería</option>
                            <option value="Herramientas" {{ old('classification', $product->classification) == 'Herramientas' ? 'selected' : '' }}>Herramientas</option>
                        </select>
                    </div>
                    
                    {{-- Precio Compra --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="precio-compra" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Precio de Compra ($):</label>
                        <input type="number" id="precio-compra" name="price_buy" min="0" step="0.01" value="{{ old('price_buy', $product->price_buy) }}" required
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>
                    
                    {{-- Precio Venta --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="precio-venta" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Precio de Venta (Mínimo requerido $):</label>
                        <input type="number" id="precio-venta" name="price_sell" min="0" step="0.01" value="{{ old('price_sell', $product->price_sell) }}" required
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>
                    
                    {{-- Stock Actual --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="stock-actual" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Stock Actual (Existencias):</label>
                        <input type="number" id="stock-actual" name="stock_initial" min="0" value="{{ old('stock_initial', $product->stock_initial) }}" required
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>
                    
                    {{-- Stock Mínimo --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="stock-minimo" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Stock Mínimo (Alerta):</label>
                        <input type="number" id="stock-minimo" name="stock_minimum" min="0" value="{{ old('stock_minimum', $product->stock_minimum) }}" required
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>

                    {{-- Vencimiento (Full Width) --}}
                    <div style="display: flex; flex-direction: column; grid-column: 1 / -1;"> 
                        <label for="fecha-vencimiento" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Fecha de Vencimiento:</label>
                        <input 
                            type="date" 
                            id="fecha-vencimiento" 
                            name="expiration_date" 
                            value="{{ old('expiration_date', $product->expiration_date ? \Carbon\Carbon::parse($product->expiration_date)->format('Y-m-d') : '') }}"
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;"
                        >
                    </div>
                </div>
                
                <div style="text-align: right; padding-top: 20px;">
                    {{-- Botones con estilos inline para asegurar el color y tamaño --}}
                    <a href="{{ route('inventory.index') }}" 
                       style="padding: 10px 25px; border: none; border-radius: 4px; font-size: 1em; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; background-color: #dc3545; color: white; transition: background-color 0.2s;">
                       Cancelar
                    </a>
                    <button type="submit" 
                       style="padding: 10px 25px; border: none; border-radius: 4px; font-size: 1em; cursor: pointer; font-weight: 600; background-color: #28a745; color: white; margin-left: 10px; transition: background-color 0.2s;">
                       Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tu lógica de JavaScript para sugerir el precio de venta sigue intacta
    document.addEventListener('DOMContentLoaded', function() {
        const precioCompraInput = document.getElementById('precio-compra');
        const precioVentaInput = document.getElementById('precio-venta');
        
        if (precioCompraInput && precioVentaInput) {
            precioCompraInput.addEventListener('input', function() {
                const precioCompra = parseFloat(this.value);
                
                if (isNaN(precioCompra) || precioCompra <= 0) {
                    return;
                }
                
                const precioVentaMinimo = precioCompra * 1.3;
                
                if (precioVentaInput.value === '' || parseFloat(precioVentaInput.value) < precioVentaMinimo) {
                    precioVentaInput.value = precioVentaMinimo.toFixed(2);
                }
            });
        }
    });
</script>
@endsection