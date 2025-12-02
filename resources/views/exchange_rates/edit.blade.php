@extends('layouts.app')

@section('title', 'Editar Tasa de Cambio | Sistema de Administración')

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
            <h3>EDITAR TASA DE CAMBIO</h3>
            
            @if(session('error'))
                <div style="background-color: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('exchange_rates.update', $exchangeRate->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <label for="date">Fecha</label>
                <input type="date" id="date" name="date" value="{{ old('date', $exchangeRate->date->format('Y-m-d')) }}" required>
                @error('date')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="base_currency">Moneda Base</label>
                <input type="text" id="base_currency" name="base_currency" value="{{ old('base_currency', $exchangeRate->base_currency) }}" maxlength="3" required style="text-transform: uppercase;">
                @error('base_currency')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="target_currency">Moneda Destino</label>
                <input type="text" id="target_currency" name="target_currency" value="{{ old('target_currency', $exchangeRate->target_currency) }}" maxlength="3" required style="text-transform: uppercase;">
                @error('target_currency')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="rate">Tasa de Cambio</label>
                <input type="number" id="rate" name="rate" value="{{ old('rate', $exchangeRate->rate) }}" step="0.0001" min="0" required>
                @error('rate')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <button type="submit" class="btn-listo">Actualizar Tasa</button>
                <a href="{{ route('exchange_rates.index') }}" class="btn-cancelar" style="display: inline-block; text-align: center; margin-top: 10px;">Cancelar</a>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Información de la Tasa</h2>
        </div>

        <div style="background-color: #ecf0f1; padding: 20px; border-radius: 8px;">
            <p><strong>ID:</strong> {{ $exchangeRate->id }}</p>
            <p><strong>Fecha:</strong> {{ $exchangeRate->date->format('d/m/Y') }}</p>
            <p><strong>Par de Monedas:</strong> {{ $exchangeRate->base_currency }}/{{ $exchangeRate->target_currency }}</p>
            <p><strong>Tasa:</strong> {{ number_format($exchangeRate->rate, 4) }}</p>
            <p><strong>Registrado:</strong> {{ $exchangeRate->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Última Actualización:</strong> {{ $exchangeRate->updated_at->format('d/m/Y H:i') }}</p>
            
            @if($exchangeRate->cashRegisters()->count() > 0)
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #bdc3c7;">
                <p style="color: #f39c12; font-weight: bold;">⚠️ Esta tasa está siendo usada en {{ $exchangeRate->cashRegisters()->count() }} caja(s) registradora(s)</p>
            @endif
        </div>
    </main>
</div>
@endsection
