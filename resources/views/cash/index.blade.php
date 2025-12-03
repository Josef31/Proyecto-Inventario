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
            <p class="rol-usuario">{{ auth()->user()->role ? auth()->user()->role->name : 'Administrador' }}</p>
        </div>

        <div class="seccion-formulario">
            <h3 id="titulo-caja">{{ $openCashRegister ? 'CIERRE DE CAJA' : 'APERTURA DE CAJA' }}</h3>
            
            @if(!$openCashRegister)
            <div id="panel-apertura">
                <form action="{{ route('cash.open') }}" method="POST">
                    @csrf
                    
                    <label for="exchange-rate">Tasa de Cambio:</label>
                    <select id="exchange-rate" name="exchange_rate_id" required>
                        @if($currentExchangeRate)
                            <option value="{{ $currentExchangeRate->id }}">
                                1 USD = {{ number_format($currentExchangeRate->rate, 2) }} Bs
                            </option>
                        @else
                            <option value="">No hay tasa de cambio disponible</option>
                        @endif
                    </select>
                    
                    <label for="monto-inicial-bs">Monto Inicial (Bolívares):</label>
                    <input type="number" id="monto-inicial-bs" name="initial_amount_bs" min="0" step="0.01" value="0.00" placeholder="0.00" required>
                    
                    <label for="monto-inicial-usd">Monto Inicial (Dólares):</label>
                    <input type="number" id="monto-inicial-usd" name="initial_amount_usd" min="0" step="0.01" value="0.00" placeholder="0.00" required>
                    
                    <button type="submit" class="btn-listo">Abrir Caja</button>
                </form>
            </div>
            @endif

            @if($openCashRegister)
            <div id="panel-cierre">
                <p><strong>Caja abierta desde:</strong><br>
                <span id="fecha-apertura-display">{{ $openCashRegister->created_at->format('d/m/Y H:i') }}</span></p>
                
                <p><strong>Tasa de Cambio:</strong><br>
                <span>1 USD = {{ number_format($openCashRegister->exchangeRate->rate ?? 0, 2) }} Bs</span></p>
                
                <hr>
                <h4>Resumen del día</h4>
                
                <div class="currency-section">
                    <h5>💵 Bolívares (Bs)</h5>
                    <div class="detalle-pago">
                        <span>Fondo Inicial:</span>
                        <span>Bs {{ number_format($openCashRegister->initial_amount_moneda1, 2) }}</span>
                    </div>
                    <div class="detalle-pago">
                        <span>Ventas (Efectivo):</span>
                        <span id="ventas-efectivo-bs">Bs {{ number_format($cashSalesTodayBs, 2) }}</span>
                    </div>
                    <div class="detalle-pago total-final">
                        <span>Total Esperado:</span>
                        <span id="total-esperado-bs" >Bs {{ number_format($openCashRegister->initial_amount_moneda1 + $cashSalesTodayBs, 2) }}</span>
                    </div>
                </div>
                
                <div class="currency-section">
                    <h5>💵 Dólares (USD)</h5>
                    <div class="detalle-pago">
                        <span>Fondo Inicial:</span>
                        <span>$ {{ number_format($openCashRegister->initial_amount_moneda2, 2) }}</span>
                    </div>
                    <div class="detalle-pago">
                        <span>Ventas (Efectivo):</span>
                        <span id="ventas-efectivo-usd">$ {{ number_format($cashSalesTodayUsd, 2) }}</span>
                    </div>
                    <div class="detalle-pago total-final">
                        <span>Total Esperado:</span>
                        <span id="total-esperado-usd">$ {{ number_format($openCashRegister->initial_amount_moneda2 + $cashSalesTodayUsd, 2) }}</span>
                    </div>
                </div>
                
                <hr>
                
                <form action="{{ route('cash.close') }}" method="POST">
                    @csrf
                    <label for="monto-final-bs">Monto Final Físico (Bolívares):</label>
                    <input type="number" id="monto-final-bs" name="final_amount_bs" min="0" step="0.01" placeholder="0.00" required>
                    
                    <label for="monto-final-usd">Monto Final Físico (Dólares):</label>
                    <input type="number" id="monto-final-usd" name="final_amount_usd" min="0" step="0.01" placeholder="0.00" required>
                    
                    <label for="notes">Notas (Opcional):</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Observaciones del cierre..."></textarea>
                    
                    <button type="submit" class="btn-cancelar" id="btn-cerrar-caja">
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

        <table class="tabla-inventario tabla-cortes">
            <thead>
                <tr>
                    <th>Fecha Apertura</th>
                    <th>Fondo Inicial Bs</th>
                    <th>Ventas Bs</th>
                    <th>Final Bs</th>
                    <th>Dif. Bs</th>
                    <th>Fondo Inicial USD</th>
                    <th>Ventas USD</th>
                    <th>Final USD</th>
                    <th>Dif. USD</th>
                    <th>Cajero</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo-cortes">
                @forelse($closedCashRegisters as $corte)
                <tr>
                    <td>{{ $corte->created_at->format('d/m/Y H:i') }}</td>
                    <td>Bs {{ number_format($corte->initial_amount_moneda1, 2) }}</td>
                    <td>Bs {{ number_format($corte->cash_sales_moneda1, 2) }}</td>
                    <td>Bs {{ number_format($corte->final_amount_moneda1, 2) }}</td>
                    <td class="{{ $corte->calculateDifferenceMoneda1() >= 0 ? 'texto-positivo' : 'texto-negativo' }}">
                        Bs {{ number_format($corte->calculateDifferenceMoneda1(), 2) }}
                    </td>
                    <td>$ {{ number_format($corte->initial_amount_moneda2, 2) }}</td>
                    <td>$ {{ number_format($corte->cash_sales_moneda2, 2) }}</td>
                    <td>$ {{ number_format($corte->final_amount_moneda2, 2) }}</td>
                    <td class="{{ $corte->calculateDifferenceMoneda2() >= 0 ? 'texto-positivo' : 'texto-negativo' }}">
                        $ {{ number_format($corte->calculateDifferenceMoneda2(), 2) }}
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
                    <td colspan="11" style="text-align: center; padding: 20px;">
                        No hay cortes de caja registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</div>
@endsection

@push('styles')
<style>
.currency-section {
    background: #f8f9fa;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border-left: 4px solid #3498db;
    color: #333;
}

.currency-section h5 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 0.9em;
}

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

#panel-cierre textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-family: Arial, sans-serif;
    resize: vertical;
}

.tabla-cortes {
    font-size: 0.85em;
}

.tabla-cortes th {
    font-size: 0.9em;
    padding: 8px 4px;
}

.tabla-cortes td {
    padding: 8px 4px;
}

.currency-section h5,
.detalle-pago,
.detalle-pago span,
.detalle-pago.total-final,
.detalle-pago.total-final span {
    color: #333333 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnCerrarCaja = document.getElementById('btn-cerrar-caja');
    
    if (btnCerrarCaja) {
        btnCerrarCaja.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: '¿Cerrar caja?',
                text: '¿Está seguro de que desea cerrar la caja? Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, cerrar caja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.closest('form').submit();
                }
            });
        });
    }
});
</script>
@endpush