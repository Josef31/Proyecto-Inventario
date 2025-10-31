@extends('layouts.app')

@section('title', 'Estado de Caja | Sistema de Administración')

@section('content')
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil"></div>
            <p class="nombre-usuario">{{ auth()->user()->name }}</p>
            <p class="rol-usuario">Administrador</p>
        </div>

        <div class="seccion-formulario">
            <h3 id="titulo-caja">{{ $openCashRegister ? 'CIERRE DE CAJA' : 'APERTURA DE CAJA' }}</h3>
            
            @if(!$openCashRegister)
            <div id="panel-apertura">
                <label for="monto-inicial">Monto Inicial (Fondo de Caja):</label>
                <input type="number" id="monto-inicial" min="0" step="0.01" value="100.00">
                <button class="btn-listo" onclick="abrirCaja()">Abrir Caja</button>
            </div>
            @endif

            @if($openCashRegister)
            <div id="panel-cierre">
                <p>Caja abierta desde: <span id="fecha-apertura-display">{{ $openCashRegister->opened_at->format('d/m/Y H:i') }}</span></p>
                <hr>
                <h4>Resumen del día (Estimado)</h4>
                <div class="detalle-pago"> <p>Fondo Inicial:</p> <p id="caja-fondo">${{ number_format($openCashRegister->initial_amount, 2) }}</p> </div>
                <div class="detalle-pago"> <p>Total Ventas (Efectivo):</p> <p id="ventas-efectivo">${{ number_format($cashSalesToday, 2) }}</p> </div>
                <div class="detalle-pago total-final"> <p>Total Esperado en Caja:</p> <p id="total-esperado">${{ number_format($openCashRegister->initial_amount + $cashSalesToday, 2) }}</p> </div>
                <hr>
                <label for="monto-final-registrado">Monto Final Físico Registrado:</label>
                <input type="number" id="monto-final-registrado" min="0" step="0.01" placeholder="0.00">
                <button class="btn-cancelar" onclick="cerrarCaja()">Cerrar y Cortar Caja</button>
            </div>
            @endif
        </div>
    </aside>

    <main class="seccion-inventario"> 
        <div class="cabecera-inventario">
            <h2>Historial de Cortes de Caja</h2>
            <p class="total-invertido">Cortes Registrados: <span id="cortes-registrados">{{ $totalCortes }}</span></p>
        </div>

        <table class="tabla-inventario">
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
                @foreach($closedCashRegisters as $corte)
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
                @endforeach
            </tbody>
        </table>
    </main>
</div>
@endsection

@section('scripts')
<script>
function abrirCaja() {
    const montoInicial = parseFloat(document.getElementById('monto-inicial').value);
    
    if (!montoInicial || montoInicial < 0) {
        alert('Por favor ingrese un monto inicial válido');
        return;
    }

    fetch('/cash/open', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            initial_amount: montoInicial
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Caja abierta exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al abrir la caja');
    });
}

function cerrarCaja() {
    const montoFinal = parseFloat(document.getElementById('monto-final-registrado').value);
    
    if (!montoFinal || montoFinal < 0) {
        alert('Por favor ingrese el monto final físico registrado');
        return;
    }

    if (!confirm('¿Está seguro de que desea cerrar la caja? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch('/cash/close', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            final_amount: montoFinal
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Caja cerrada exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cerrar la caja');
    });
}

// Actualizar ventas en efectivo cada 30 segundos (solo si la caja está abierta)
@if($openCashRegister)
function actualizarVentasEfectivo() {
    fetch('/cash/today-sales')
        .then(response => response.json())
        .then(data => {
            document.getElementById('ventas-efectivo').textContent = '$' + data.cash_sales.toFixed(2);
            
            // Recalcular total esperado
            const fondoInicial = {{ $openCashRegister->initial_amount }};
            const totalEsperado = fondoInicial + data.cash_sales;
            document.getElementById('total-esperado').textContent = '$' + totalEsperado.toFixed(2);
        })
        .catch(error => console.error('Error:', error));
}

// Actualizar cada 30 segundos
setInterval(actualizarVentasEfectivo, 30000);
@endif
</script>

<style>
.texto-positivo {
    color: #4CAF50;
    font-weight: bold;
}

.texto-negativo {
    color: #f44336;
    font-weight: bold;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.badge-abierta {
    background: #4CAF50;
    color: white;
}

.badge-cerrada {
    background: #9e9e9e;
    color: white;
}

.detalle-pago {
    display: flex;
    justify-content: space-between;
    margin: 5px 0;
}

.detalle-pago.total-final {
    border-top: 1px solid #ddd;
    padding-top: 10px;
    margin-top: 10px;
    font-weight: bold;
}
</style>
@endsection