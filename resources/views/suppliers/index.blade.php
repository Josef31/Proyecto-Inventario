@extends('layouts.app')

@section('title', 'Proveedores | Sistema de Administración')

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
            <h3>NUEVO PROVEEDOR</h3>
            
            <form id="form-proveedor" method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                
                <label for="nombre">Nombre del Proveedor:</label>
                <input type="text" id="nombre" name="name" value="{{ old('name') }}" required placeholder="Ej. Distribuidora XYZ" maxlength="50">
                
                <label for="rfc">RFC:</label>
                <input type="text" id="rfc" name="rfc" value="{{ old('rfc') }}" required placeholder="Ej. ABC123456XYZ" maxlength="255">
                
                <label for="phone">Teléfono:</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="Ej. +58 412-1234567" maxlength="20">
                
                <button type="submit" class="btn-listo">Guardar Proveedor</button>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Directorio de Proveedores</h2>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>RFC</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo">
                @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->id }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->rfc ?? 'N/A' }}</td>
                        <td>{{ $supplier->phone ?? 'N/A' }}</td>
                        <td class="acciones">
                            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn-editar" style="background-color: #039438; color: white; padding: 5px 10px; border-radius: 3px; margin-right: 5px; text-decoration: none;">Editar</a>
                            
                            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" style="display: inline-block;" data-item-name="{{ $supplier->name }}" data-item-type="proveedor">
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
