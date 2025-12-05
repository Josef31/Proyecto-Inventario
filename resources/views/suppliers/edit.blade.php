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
            <h3>EDITAR PROVEEDOR (V2)</h3>
            
            <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                @csrf
                @method('PUT')
                
                <label for="nombre">Nombre del Proveedor:</label>
                <input type="text" id="nombre" name="name" value="{{ old('name', $supplier->name) }}" required placeholder="Ej. Distribuidora XYZ" maxlength="50">
                
                <label for="rfc">RFC (opcional):</label>
                <input type="text" id="rfc" name="rfc" value="{{ old('rfc', $supplier->rfc) }}" placeholder="Ej. ABC123456XYZ" maxlength="255">
                
                <label for="phone">Teléfono (opcional):</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}" placeholder="Ej. +58 412-1234567" maxlength="20">
                
                
                <button type="submit" class="btn-listo">Actualizar Proveedor</button>
                <a href="{{ route('suppliers.index') }}" class="btn-cancelar" style="display: inline-block; text-align: center; margin-top: 10px;">Cancelar</a>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Editar Proveedor</h2>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>RFC</th>
                    <th>Teléfono</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $supplier->id }}</td>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->rfc ?? 'N/A' }}</td>
                    <td>{{ $supplier->phone ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </main>
</div>
@endsection
