@extends('layouts.app')

@section('title', 'Editar Cliente | ' . $customer->name)

@section('content')

{{-- 1. Contenedor principal con barra lateral --}}
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil">
                <img class="icono" src="{{ asset('images/perfil.png') }}" alt="Usuario" width="80" height="80">
            </div>
            <p class="nombre-usuario">{{ auth()->user()->name }}</p>
            <p class="rol-usuario">{{ auth()->user()->role ?? 'Administrador' }}</p>
        </div>

        <div class="seccion-acciones-admin">
            <h3>ACCIONES</h3>
            <a href="{{ route('customers.index') }}" class="btn-admin-accion">Volver a Clientes</a>
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
                Editar Cliente: **{{ $customer->name }}**
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

            <form method="POST" action="{{ route('customers.update', $customer->id) }}">
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
                        <label for="nombre" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Nombre Completo:</label>
                        <input type="text" id="nombre" name="name" value="{{ old('name', $customer->name) }}" required 
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>

                    {{-- Email --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="email" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $customer->email) }}" 
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>
                    
                    {{-- Teléfono --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="telefono" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Teléfono:</label>
                        <input type="text" id="telefono" name="phone" value="{{ old('phone', $customer->phone) }}" 
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>
                    
                    {{-- RFC --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="rfc" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">RFC:</label>
                        <input type="text" id="rfc" name="rfc" value="{{ old('rfc', $customer->rfc) }}" 
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>
                    
                    {{-- Ciudad --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="ciudad" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Ciudad:</label>
                        <input type="text" id="ciudad" name="city" value="{{ old('city', $customer->city) }}" 
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                    </div>
                    
                    {{-- Estado (Activo/Inactivo) --}}
                    <div style="display: flex; flex-direction: column;">
                        <label for="estado" style="margin-bottom: 6px; font-weight: 500; color: #495057; font-size: 0.9em;">Estado:</label>
                        <select id="estado" name="is_active" 
                            style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; box-sizing: border-box; background-color: #fff;">
                            <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                </div>
                
                <div style="text-align: right; padding-top: 20px;">
                    <a href="{{ route('customers.index') }}" 
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
