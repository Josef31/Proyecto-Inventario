@extends('layouts.app')

@section('title', 'Facturas | Sistema de Administración')

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
            <h3>Generación de Documentos</h3>
            <p>Las facturas se generan automáticamente al finalizar una venta en el TPV.</p>
            <hr>
            <p>Total de documentos registrados: <strong id="total-facturas">{{ $totalInvoices }}</strong></p>
            <button class="btn-detalles" onclick="window.location.href='{{ route('sales.index') }}'">Ir a TPV para facturar</button>
        </div>
    </aside>

    <main class="seccion-inventario"> 
        <div class="cabecera-inventario">
            <h2>Historial de Facturas (Recibos de Venta)</h2>
            <p class="total-invertido">Utiliza el botón <strong>"Ver"</strong> para consultar el detalle de la venta.</p>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th># Factura</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th># Ítems</th>
                    <th>Método de Pago</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo-facturas">
                @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number ?? $invoice->sale_code }}</td>
                    <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $invoice->customer_name ?: 'Cliente General' }}</td>
                    <td>{{ $invoice->items_count }}</td>
                    <td>
                        <span class="badge {{ $invoice->payment_method == 'efectivo' ? 'badge-efectivo' : 'badge-tarjeta' }}">
                            {{ strtoupper($invoice->payment_method) }}
                        </span>
                    </td>
                    <td>${{ number_format($invoice->total, 2) }}</td>
                    <td>
                        <button class="btn-ver" onclick="verFactura({{ $invoice->id }})">Ver</button>
                        <button class="btn-imprimir" onclick="imprimirFactura({{ $invoice->id }})">Imprimir</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>

<!-- Modal para ver detalles de factura -->
<div id="modal-factura" class="modal" style="display: none;">
    <div class="modal-contenido modal-grande">
        <h3>Detalle de Factura</h3>
        <div id="contenido-factura">
            <!-- Contenido de la factura se cargará aquí -->
        </div>
        <div class="modal-botones">
            <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cerrar</button>
            <button type="button" class="btn-imprimir" onclick="imprimirDesdeModal()">Imprimir</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let facturaActual = null;

function verFactura(id) {
    fetch(`/invoices/${id}/details`)
        .then(response => response.json())
        .then(invoice => {
            facturaActual = invoice;
            mostrarDetallesFactura(invoice);
            document.getElementById('modal-factura').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles de la factura');
        });
}

function mostrarDetallesFactura(invoice) {
    const contenido = document.getElementById('contenido-factura');
    
    const itemsHtml = invoice.items.map(item => `
        <tr>
            <td>${item.product_name}</td>
            <td>$${parseFloat(item.price).toFixed(2)}</td>
            <td>${item.quantity}</td>
            <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
        </tr>
    `).join('');

    contenido.innerHTML = `
        <div class="factura-detalle">
            <div class="factura-header">
                <div class="factura-info">
                    <p><strong>Factura:</strong> ${invoice.invoice_number || invoice.sale_code}</p>
                    <p><strong>Fecha:</strong> ${new Date(invoice.created_at).toLocaleDateString('es-ES')}</p>
                    <p><strong>Cliente:</strong> ${invoice.customer_name || 'Cliente General'}</p>
                    <p><strong>RFC:</strong> ${invoice.customer_rfc || 'N/A'}</p>
                </div>
                <div class="factura-vendedor">
                    <p><strong>Vendedor:</strong> ${invoice.user.name}</p>
                    <p><strong>Método de Pago:</strong> ${invoice.payment_method.toUpperCase()}</p>
                </div>
            </div>
            
            <table class="tabla-detalle">
                <thead>
                    <tr>
                        <th>Producto/Servicio</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                        <td><strong>$${parseFloat(invoice.subtotal).toFixed(2)}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>IVA (16%):</strong></td>
                        <td><strong>$${parseFloat(invoice.taxes).toFixed(2)}</strong></td>
                    </tr>
                    <tr class="total-final">
                        <td colspan="3" style="text-align: right;"><strong>TOTAL:</strong></td>
                        <td><strong>$${parseFloat(invoice.total).toFixed(2)}</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            ${invoice.payment_method === 'efectivo' ? `
            <div class="pago-efectivo">
                <p><strong>Monto Recibido:</strong> $${parseFloat(invoice.amount_received).toFixed(2)}</p>
                <p><strong>Cambio:</strong> $${parseFloat(invoice.change).toFixed(2)}</p>
            </div>
            ` : ''}
        </div>
    `;
}

function imprimirFactura(id) {
    if (confirm('¿Desea descargar la factura en formato PDF?')) {
        window.open(`/invoices/${id}/print`, '_blank');
    }
}

function imprimirDesdeModal() {
    if (facturaActual) {
        imprimirFactura(facturaActual.id);
    }
}

function cerrarModal() {
    document.getElementById('modal-factura').style.display = 'none';
    facturaActual = null;
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('modal-factura');
    if (event.target === modal) {
        cerrarModal();
    }
}
</script>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-contenido {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    max-width: 90%;
    max-height: 90%;
    overflow-y: auto;
}

.modal-grande {
    width: 800px;
}

.modal-botones {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    justify-content: flex-end;
}

.btn-ver {
    background: #2196F3;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 5px;
}

.btn-imprimir {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-ver:hover { background: #1976D2; }
.btn-imprimir:hover { background: #45a049; }

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
}

.badge-efectivo {
    background: #4CAF50;
    color: white;
}

.badge-tarjeta {
    background: #2196F3;
    color: white;
}

.factura-detalle {
    font-family: Arial, sans-serif;
}

.factura-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #333;
}

.factura-info, .factura-vendedor {
    flex: 1;
}

.tabla-detalle {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.tabla-detalle th,
.tabla-detalle td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.tabla-detalle th {
    background-color: #f5f5f5;
    font-weight: bold;
}

.tabla-detalle tfoot tr.total-final td {
    background-color: #e8f5e8;
    font-size: 1.1em;
}

.pago-efectivo {
    background: #f9f9f9;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
}
</style>
@endsection