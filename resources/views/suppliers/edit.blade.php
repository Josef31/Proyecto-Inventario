@extends('layouts.app')

@section('title', 'Editar Proveedor | Sistema de Administración')

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
            <h3>EDITAR PROVEEDOR</h3>
            
            <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                @csrf
                @method('PUT')
                
                <label for="nombre">Nombre del Proveedor:</label>
                <input type="text" id="nombre" name="name" value="{{ old('name', $supplier->name) }}" required placeholder="Ej. Distribuidora XYZ" maxlength="50">
                
                <button type="submit" class="btn-listo">Actualizar Proveedor</button>
                <a href="{{ route('suppliers.index') }}" class="btn-cancelar" style="display: inline-block; text-align: center; margin-top: 10px;">Cancelar</a>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Editar Proveedor: {{ $supplier->name }}</h2>
            
            @if (session('error'))
                <div class="alerta-error" style="display: flex; background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                    <span class="icono-ayuda">✗</span>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="alerta-error" style="display: flex; background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                    <span class="icono-ayuda">✗</span>
                    <p>Error de validación: {{ $errors->first() }}</p>
                </div>
            @endif
        </div>

        <div style="padding: 20px;">
            <p><strong>ID:</strong> {{ $supplier->id }}</p>
            <p><strong>Nombre Actual:</strong> {{ $supplier->name }}</p>
        </div>
    </main>
</div>
@endsection
