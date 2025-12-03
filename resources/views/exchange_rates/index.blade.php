@extends('layouts.app')

@section('title', 'Tasas de Cambio | Sistema de Administración')

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
            <h3>CREAR TASA DE CAMBIO</h3>
            
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

            <form action="{{ route('exchange_rates.store') }}" method="POST">
                @csrf
                
                <label for="date">Fecha</label>
                <input type="date" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="base_currency">Moneda Base</label>
                <input type="text" id="base_currency" name="base_currency" value="{{ old('base_currency', 'USD') }}" maxlength="3" required style="text-transform: uppercase;">
                @error('base_currency')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="target_currency">Moneda Destino</label>
                <input type="text" id="target_currency" name="target_currency" value="{{ old('target_currency', 'VES') }}" maxlength="3" required style="text-transform: uppercase;">
                @error('target_currency')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <label for="rate">Tasa de Cambio</label>
                <input type="number" id="rate" name="rate" value="{{ old('rate') }}" step="0.0001" min="0" required>
                @error('rate')
                    <span style="color: #e74c3c; font-size: 0.85em;">{{ $message }}</span>
                @enderror

                <button type="submit" class="btn-listo">Crear Tasa</button>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Tasas de Cambio</h2>
            @if($latestRate)
                <p class="total-invertido">
                    Tasa Actual: <strong>{{ $latestRate->base_currency }}/{{ $latestRate->target_currency }} = {{ number_format($latestRate->rate, 4) }}</strong>
                    <span style="font-size: 0.85em; color: #7f8c8d;">({{ $latestRate->date->format('d/m/Y') }})</span>
                </p>
            @else
                <p class="total-invertido">No hay tasas registradas</p>
            @endif
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Moneda Base</th>
                    <th>Moneda Destino</th>
                    <th>Tasa</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exchangeRates as $rate)
                <tr>
                    <td>{{ $rate->id }}</td>
                    <td>{{ $rate->date->format('d/m/Y') }}</td>
                    <td>{{ $rate->base_currency }}</td>
                    <td>{{ $rate->target_currency }}</td>
                    <td><strong>{{ number_format($rate->rate, 4) }}</strong></td>
                    <td>{{ $rate->created_at->format('d/m/Y H:i') }}</td>
                    <td class="acciones">
                        <a href="{{ route('exchange_rates.edit', $rate->id) }}" class="btn-editar">Editar</a>
                        
                        <form action="{{ route('exchange_rates.destroy', $rate->id) }}" method="POST" style="display: inline;" data-item-name="{{ $rate->base_currency }}/{{ $rate->target_currency }}" data-item-type="tasa de cambio">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #999;">No hay tasas de cambio registradas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</div>
@endsection
