@extends('layouts.app')

@section('title', 'Clientes | Sistema de Administración')

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
            <h3>NUEVO CLIENTE</h3>
            
            <form id="form-cliente" method="POST" action="{{ route('customers.store') }}">
                @csrf
                
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="name" value="{{ old('name') }}" required placeholder="Ej. Juan Pérez">
                
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="cliente@ejemplo.com">
                
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="phone" value="{{ old('phone') }}" placeholder="Ej. 555-123-4567">
                
                <label for="rfc">RFC:</label>
                <input type="text" id="rfc" name="rfc" value="{{ old('rfc') }}" placeholder="RFC del cliente">
                
                <label for="ciudad">Ciudad:</label>
                <input type="text" id="ciudad" name="city" value="{{ old('city') }}" placeholder="Ciudad de residencia">
                
                <button type="submit" class="btn-listo">Guardar Cliente</button>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Directorio de Clientes</h2>
            
            {{-- Mensajes de Sesión --}}
            @if (session('success'))
                <div class="alerta-exito" style="display: flex;">
                    <span class="icono-ayuda">✓</span>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
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

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>RFC</th>
                    <th>Ciudad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo">
                @foreach($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email ?? 'N/A' }}</td>
                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                        <td>{{ $customer->rfc ?? 'N/A' }}</td>
                        <td>{{ $customer->city ?? 'N/A' }}</td>
                        <td>
                            @if($customer->is_active)
                                <span style="color: green; font-weight: bold;">Activo</span>
                            @else
                                <span style="color: red; font-weight: bold;">Inactivo</span>
                            @endif
                        </td>
                        <td class="acciones">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn-editar" style="background-color: #039438; color: white; padding: 5px 10px; border-radius: 3px; margin-right: 5px; text-decoration: none;">Editar</a>
                            
                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este cliente?');">
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
