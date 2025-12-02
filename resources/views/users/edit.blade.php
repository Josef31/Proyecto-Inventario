@extends('layouts.app')

@section('title', 'Editar Usuario | Sistema de Administración')

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
            <h3>EDITAR USUARIO</h3>
            
            @if(session('error'))
                <div style="background-color: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <label for="name">Nombre Completo</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="password">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                <input type="password" id="password" name="password">
                @error('password')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="id_role">Rol</label>
                <select id="id_role" name="id_role" required {{ $user->isAdmin() ? 'disabled' : '' }}>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('id_role', $user->id_role) == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @if($user->isAdmin())
                    <input type="hidden" name="id_role" value="{{ $user->id_role }}">
                    <span style="color: #f39c12; font-size: 0.85em;">No se puede cambiar el rol del administrador</span>
                @endif
                @error('id_role')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <button type="submit" class="btn-listo">Actualizar Usuario</button>
                <a href="{{ route('users.index') }}" class="btn-cancelar" style="display: inline-block; text-align: center; margin-top: 10px;">Cancelar</a>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Información del Usuario</h2>
        </div>

        <div style="background-color: #ecf0f1; padding: 20px; border-radius: 8px;">
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>Nombre:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Rol:</strong> {{ $user->role->name ?? 'Sin rol' }}</p>
            <p><strong>Fecha de Registro:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Última Actualización:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
            
            @if($user->isAdmin())
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #bdc3c7;">
                <p style="color: #e74c3c; font-weight: bold;">⚠️ Este es el usuario administrador principal del sistema</p>
            @endif
        </div>
    </main>
</div>
@endsection
