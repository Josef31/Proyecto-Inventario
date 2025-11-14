@extends('layouts.app')

@section('title', 'Estado de Caja | Sistema de Administración')

@section('content')
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil">
                <img class="icono" src="{{ asset('images/perfil.png') }}" alt="Usuario" width="80" height="80">
            </div>
            <p class="nombre-usuario">{{ auth()->user()->name }}</p>
            <p class="rol-usuario">Administrador</p>
        </div>

        <div class="seccion-formulario">
            <h3 id="titulo-caja">{{ $openCashRegister ? 'CIERRE DE CAJA' : 'APERTURA DE CAJA' }}</h3>
            
            @if(!$openCashRegister)
            <div id="panel-apertura">
                <form action="{{ route('cash.open') }}" method="POST">
                    @csrf
                    <label for="monto-inicial">Monto Inicial (Fondo de Caja):</label>
                    <input type="number" id="monto-inicial" name="initial_amount" min="0" step="0.01" value="100.00" placeholder="0.00" required>
                    <button type="submit" class="btn-listo">Abrir Caja</button>
                </form>
            </div>
            @endif

            @if($openCashRegister)
            <div id="panel-cierre">
                <p><strong>Caja abierta desde:</strong><br>
                <span id="fecha-apertura-display">{{ $openCashRegister->opened_at->format('d/m/Y H:i') }}</span></p>
                <hr>
                <h4>Resumen del día</h4>
                <div class="detalle-pago"> 
                    <span>Fondo Inicial:</span> 
                    <span id="caja-fondo">${{ number_format($openCashRegister->initial_amount, 2) }}</span> 
                </div>
                <div class="detalle-pago"> 
                    <span>Total Ventas (Efectivo):</span> 
                    <span id="ventas-efectivo">${{ number_format($cashSalesToday, 2) }}</span> 
                </div>
                <div class="detalle-pago total-final"> 
                    <span>Total Esperado en Caja:</span> 
                    <span id="total-esperado">${{ number_format($openCashRegister->initial_amount + $cashSalesToday, 2) }}</span> 
                </div>
                <hr>
                
                <form action="{{ route('cash.close') }}" method="POST">
                    @csrf
                    <label for="monto-final-registrado">Monto Final Físico Registrado:</label>
                    <input type="number" id="monto-final-registrado" name="final_amount" min="0" step="0.01" placeholder="0.00" required>
                    <button type="submit" class="btn-cancelar" onclick="return confirm('¿Está seguro de que desea cerrar la caja? Esta acción no se puede deshacer.')">
                        Cerrar y Cortar Caja
                    </button>
                </form>
            </div>
            @endif
        </div>
    </aside>

    <main class="seccion-inventario"> 
        <div class="cabecera-inventario">
            <h2>Historial de Cortes de Caja</h2>
            <p class="total-invertido">Cortes Registrados: <span id="cortes-registrados">{{ $totalCortes }}</span></p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <table class="tabla-inventario tabla-cortes">
            <thead>
                <tr>
                    <th>Fecha de Apertura</th>
                    <th>Fondo Inicial</th>
                    <th>Ventas Registradas</th>
                    <th>Monto Final Físico</th>
                    <th>Diferencia</th>
                    <th>Cajero</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo-cortes">
                @forelse($closedCashRegisters as $corte)
                <tr>
                    <td>{{ $corte->opened_at->format('d/m/Y H:i') }}</td>
                    <td>${{ number_format($corte->initial_amount, 2) }}</td>
                    <td>${{ number_format($corte->cash_sales, 2) }}</td>
                    <td>${{ number_format($corte->final_amount, 2) }}</td>
                    <td class="{{ $corte->difference >= 0 ? 'texto-positivo' : 'texto-negativo' }}">
                        ${{ number_format($corte->difference, 2) }}
                    </td>
                    <td>{{ $corte->user->name }}</td>
                    <td>
                        <span class="badge {{ $corte->status == 'cerrada' ? 'badge-cerrada' : 'badge-abierta' }}">
                            {{ $corte->status == 'cerrada' ? 'CERRADA' : 'ABIERTA' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        No hay cortes de caja registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</div>
@endsection

@section('styles')
<style>
.texto-positivo {
    color: #28a745;
    font-weight: bold;
    background-color: #f8fff9;
    padding: 4px 8px;
    border-radius: 4px;
}

.texto-negativo {
    color: #dc3545;
    font-weight: bold;
    background-color: #fff5f5;
    padding: 4px 8px;
    border-radius: 4px;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.badge-abierta {
    background: #28a745;
    color: white;
}

.badge-cerrada {
    background: #6c757d;
    color: white;
}

.detalle-pago {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
    font-size: 0.9em;
}

.detalle-pago.total-final {
    border-top: 2px solid #34495e;
    padding-top: 12px;
    margin-top: 12px;
    font-weight: bold;
    font-size: 1em;
}

#panel-cierre {
    font-size: 0.9em;
}

#panel-cierre hr {
    margin: 15px 0;
    border: none;
    border-top: 1px solid #4a6681;
}

.alert {
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
@endsection