@extends('layouts.app')

@section('title', 'Usuarios | Sistema de Administración')

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
            <h3>CREAR NUEVO USUARIO</h3>
            
            @if(session('success'))
                <div style="background-color: #2ecc71; color: white; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background-color: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                <label for="name">Nombre Completo</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="id_role">Rol</label>
                <select id="id_role" name="id_role" required>
                    <option value="">Seleccionar rol</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('id_role') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('id_role')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <button type="submit" class="btn-listo">Crear Usuario</button>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Gestión de Usuarios</h2>
            <p class="total-invertido">Total de usuarios: <strong>{{ $users->count() }}</strong></p>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha de Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; {{ $user->isAdmin() ? 'background-color: #e74c3c; color: white;' : 'background-color: #3498db; color: white;' }}">
                            {{ $user->role->name ?? 'Sin rol' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="acciones">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn-editar">Editar</a>
                        
                        @if(!$user->isAdmin())
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;" data-item-name="{{ $user->name }}" data-item-type="usuario">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        @else
                            <button class="btn-eliminar" disabled style="opacity: 0.5; cursor: not-allowed;" title="No se puede eliminar el administrador">Eliminar</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>
@endsection
