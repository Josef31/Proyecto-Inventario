@extends('layouts.app')

@section('title', 'Ventas | Sistema de Administración')

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
            <h3>DETALLES DE CLIENTE</h3>
            <input type="text" id="cliente-nombre" placeholder="Nombre del Cliente">
            <input type="text" id="cliente-rfc" placeholder="RFC/Identificación">
            <button class="btn-listo">Asignar Cliente</button>
        </div>
    </aside>

    <main class="seccion-ventas">
        <div class="cabecera-ventas">
            <h2>Punto de Venta (TPV)</h2>
        </div>

        <div class="tpv-grid">
            <div class="tpv-col-izquierda">

                <div class="buscador-productos">
                    <!-- CORRECCIÓN: id correcto y evento correcto -->
                    <select id="select-producto" onchange="seleccionarProducto()">
                        <option value="" disabled selected>--- Seleccionar Producto ---</option>
                        @foreach ($products as $product)
                        <option
                            value="{{ $product['id'] }}"
                            data-name="{{ $product['name'] }}"
                            data-price="{{ $product['price'] }}"
                            data-stock="{{ $product['stock'] }}">
                            ID: {{ $product['id'] }} - {{ $product['name'] }} (${{ $product['price'] }}) (Stock: {{ $product['stock'] }})
                        </option>
                        @endforeach
                    </select>
                </div>


                <div class="lista-carrito">
                    <h3>Detalle de la Venta (Carrito)</h3>
                    <table class="tabla-carrito">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo-carrito">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tpv-col-derecha">

                <div class="resumen-pago">
                    <div class="detalle-pago">
                        <p>Subtotal:</p>
                        <p id="total-subtotal">0.00</p>
                    </div>
                    <div class="detalle-pago">
                        <p>Impuestos (IVA 16%):</p>
                        <p id="total-impuestos">0.00</p>
                    </div>
                    <div class="detalle-pago total-final">
                        <p>TOTAL A PAGAR:</p>
                        <p class="valor-total" id="total-pagar">0.00</p>
                    </div>
                </div>

                <div class="opciones-pago">
                    <h4>Método de Pago</h4>
                    <select id="metodo-pago">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                    </select>
                    <input type="number" id="monto-recibido" placeholder="Monto Recibido" min="0" step="0.01">

                    <div class="detalle-pago">
                        <p>Su Cambio:</p>
                        <p id="monto-cambio">0.00</p>
                    </div>
                </div>

                <div class="botones-acciones-tpv">
                    <button class="btn-accion btn-cobrar" onclick="finalizarVenta()">COBRAR VENTA</button>
                    <button class="btn-accion btn-cancelar" onclick="cancelarVenta()">CANCELAR</button>
                </div>
            </div>
        </div>
    </main>
</div>



<script>
// =======================================================
// 0. PROTECCIÓN CONTRA EVENTOS DUPLICADOS
// =======================================================
// Remover event listeners duplicados si existen
function limpiarEventListeners() {
    const select = document.getElementById('select-producto');
    const montoRecibido = document.getElementById('monto-recibido');
    
    if (select) {
        const newSelect = select.cloneNode(true);
        select.parentNode.replaceChild(newSelect, select);
    }
    
    if (montoRecibido) {
        const newMonto = montoRecibido.cloneNode(true);
        montoRecibido.parentNode.replaceChild(newMonto, montoRecibido);
    }
}
// =======================================================
// 1. VARIABLES GLOBALES
// =======================================================
let saleCarrito = [];
const SALE_IVA_RATE = 0.16;

// =======================================================
// 2. FUNCIÓN DE SELECCIÓN DE PRODUCTO
// =======================================================
function seleccionarProducto() {
    console.log('🚨 VERSIÓN EMERGENCIA ACTIVADA');
    
    // Método directo que SÍ funciona
    const select = document.getElementById('select-producto');
    const option = select.options[select.selectedIndex];
    
    // Agregar directamente al carrito
    saleCarrito.push({
        id: parseInt(option.value),
        nombre: option.getAttribute('data-name'),
        precio: parseFloat(option.getAttribute('data-price')),
        cantidad: 1,
        stockMaximo: parseInt(option.getAttribute('data-stock'))
    });
    
    // Reset y actualizar
    select.selectedIndex = 0;
    actualizarCarrito();
    
    console.log('✅ Producto agregado (modo emergencia)');
}

// También fuerza la inicialización
const select = document.getElementById('select-producto');
if (select) {
    select.onchange = seleccionarProducto;
    console.log('✅ Evento reassignado');
}

// =======================================================
// 3. ACTUALIZAR CARRITO - VERSIÓN CON VALIDACIÓN EXTREMA
// =======================================================
function actualizarCarrito() {
    console.log('🔄 Actualizando carrito...');
    
    const cuerpo = document.getElementById('cuerpo-carrito');
    if (!cuerpo) {
        console.error('❌ No se encuentra cuerpo-carrito');
        return;
    }
    
    // GUARDAR referencia al carrito actual para debug
    console.log('📦 Carrito actual:', saleCarrito);
    
    // LIMPIAR COMPLETAMENTE el cuerpo
    cuerpo.innerHTML = '';
    
    let subtotal = 0;

    // Si el carrito está vacío, solo actualizar totales a cero
    if (saleCarrito.length === 0) {
        console.log('🛒 Carrito vacío');
        document.getElementById('total-subtotal').textContent = '0.00';
        document.getElementById('total-impuestos').textContent = '0.00';
        document.getElementById('total-pagar').textContent = '0.00';
        // SOLO calcular cambio, no hacer nada más
        calcularCambio();
        return;
    }
    
    console.log('📦 Renderizando', saleCarrito.length, 'productos');
    
    // Renderizar CADA ITEM individualmente
    saleCarrito.forEach(item => {
        // Validar que el item tenga datos válidos
        if (!item || typeof item !== 'object') {
            console.error('❌ Item inválido:', item);
            return;
        }
        
        const precio = Number(item.precio) || 0;
        const cantidad = Number(item.cantidad) || 0;
        const itemSubtotal = precio * cantidad;
        subtotal += itemSubtotal;

        console.log('📝 Agregando item:', item.nombre, precio, cantidad, itemSubtotal);

        // Crear fila NUEVA
        const tr = document.createElement('tr');
        
        // Crear celdas INDIVIDUALMENTE para evitar problemas
        const tdNombre = document.createElement('td');
        tdNombre.textContent = item.nombre || 'Sin nombre';
        
        const tdCantidad = document.createElement('td');
        const inputCantidad = document.createElement('input');
        inputCantidad.type = 'number';
        inputCantidad.value = cantidad;
        inputCantidad.min = '1';
        inputCantidad.max = item.stockMaximo || '999';
        inputCantidad.style = 'width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 3px;';
        inputCantidad.onchange = function() { actualizarCantidad(item.id, this.value); };
        tdCantidad.appendChild(inputCantidad);
        
        const tdPrecio = document.createElement('td');
        tdPrecio.textContent = `$${precio.toFixed(2)}`;
        
        const tdSubtotal = document.createElement('td');
        tdSubtotal.textContent = `$${itemSubtotal.toFixed(2)}`;
        
        const tdAccion = document.createElement('td');
        const btnEliminar = document.createElement('button');
        btnEliminar.textContent = '×';
        btnEliminar.style = 'background-color: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-weight: bold;';
        btnEliminar.onclick = function() { eliminarDelCarrito(item.id); };
        tdAccion.appendChild(btnEliminar);
        
        // Agregar celdas a la fila
        tr.appendChild(tdNombre);
        tr.appendChild(tdCantidad);
        tr.appendChild(tdPrecio);
        tr.appendChild(tdSubtotal);
        tr.appendChild(tdAccion);
        
        // Agregar fila al cuerpo
        cuerpo.appendChild(tr);
    });
    
    // Calcular totales
    const impuestos = subtotal * SALE_IVA_RATE;
    const total = subtotal + impuestos;
    
    console.log('💰 Totales:', subtotal, impuestos, total);
    
    // Actualizar UI
    document.getElementById('total-subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('total-impuestos').textContent = impuestos.toFixed(2);
    document.getElementById('total-pagar').textContent = total.toFixed(2);
    
    calcularCambio();
}

// =======================================================
// 4. FUNCIONES BÁSICAS DEL CARRITO
// =======================================================
function eliminarDelCarrito(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
        saleCarrito = saleCarrito.filter(item => item.id !== id);
        actualizarCarrito();
    }
}

function actualizarCantidad(id, nuevaCantidad) {
    const cantidad = parseInt(nuevaCantidad);
    if (cantidad <= 0 || isNaN(cantidad)) {
        eliminarDelCarrito(id);
        return;
    }
    
    const item = saleCarrito.find(item => item.id === id);
    if (item) {
        item.cantidad = cantidad;
        actualizarCarrito();
    }
}

// =======================================================
// 5. PAGO Y EVENTOS
// =======================================================
function calcularCambio() {
    console.log('💰 Calculando cambio...');
    
    const montoRecibido = document.getElementById('monto-recibido');
    const totalPagar = document.getElementById('total-pagar');
    const montoCambio = document.getElementById('monto-cambio');
    const metodoPago = document.getElementById('metodo-pago');
    const btnCobrar = document.querySelector('.btn-cobrar');

    if (!montoRecibido || !totalPagar || !montoCambio || !metodoPago || !btnCobrar) {
        console.error('❌ Elementos de pago no encontrados');
        return;
    }

    const monto = parseFloat(montoRecibido.value) || 0;
    const total = parseFloat(totalPagar.textContent) || 0;
    const metodo = metodoPago.value;

    console.log('📊 Datos para cálculo:', { monto, total, metodo });

    let cambio = 0;
    let esValido = false;

    if (metodo === 'efectivo') {
        cambio = monto - total;
        esValido = monto >= total;
        console.log('💵 Efectivo - Cambio:', cambio, 'Válido:', esValido);
    } else {
        cambio = 0;
        esValido = total > 0;
        console.log('💳 Tarjeta - Válido:', esValido);
    }

    montoCambio.textContent = Math.max(0, cambio).toFixed(2);

    if (esValido && total > 0) {
        btnCobrar.disabled = false;
        btnCobrar.style.backgroundColor = '#2ecc71';
        console.log('✅ Botón cobrar habilitado');
    } else {
        btnCobrar.disabled = true;
        btnCobrar.style.backgroundColor = '#cccccc';
        console.log('❌ Botón cobrar deshabilitado');
    }
}

// =======================================================
// 6. FUNCIONES DE VENTA
// =======================================================
async function finalizarVenta() {
    if (saleCarrito.length === 0) {
        alert('El carrito está vacío. Agrega productos para cobrar.');
        return;
    }

    const total = parseFloat(document.getElementById('total-pagar').textContent);
    const metodoPago = document.getElementById('metodo-pago').value;
    const montoRecibido = parseFloat(document.getElementById('monto-recibido').value) || 0;

    if (metodoPago === 'efectivo' && montoRecibido < total) {
        alert('El monto recibido es menor al total a pagar.');
        return;
    }

    const customerName = document.getElementById('cliente-nombre') ? document.getElementById('cliente-nombre').value : '';
    const customerRfc = document.getElementById('cliente-rfc') ? document.getElementById('cliente-rfc').value : '';

    const ventaData = {
        items: saleCarrito.map(item => ({
            id: item.id,
            price: item.precio,
            quantity: item.cantidad
        })),
        customer_name: customerName,
        customer_rfc: customerRfc,
        payment_method: metodoPago,
        amount_received: montoRecibido,
    };

    try {
        const response = await fetch('/sales/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(ventaData)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert(`✅ Venta ${result.sale_code} procesada exitosamente.\nTotal: $${result.total.toFixed(2)}`);
            cancelarVenta();
        } else {
            alert('❌ Error al procesar la venta: ' + (result.message || 'Error desconocido.'));
        }

    } catch (error) {
        console.error('Error al enviar la venta:', error);
        alert('❌ Error de conexión con el servidor al procesar la venta.');
    }
}

function cancelarVenta() {
    saleCarrito = [];
    actualizarCarrito();

    const select = document.getElementById('select-producto');
    if (select) select.selectedIndex = 0;

    const montoRecibido = document.getElementById('monto-recibido');
    if (montoRecibido) montoRecibido.value = '';

    const clienteNombre = document.getElementById('cliente-nombre');
    if (clienteNombre) clienteNombre.value = '';

    const clienteRfc = document.getElementById('cliente-rfc');
    if (clienteRfc) clienteRfc.value = '';

    calcularCambio();
}

// =======================================================
// 7. INICIALIZACIÓN
// =======================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ TPV INICIALIZADO - VERSIÓN ESTABLE');
    
    const select = document.getElementById('select-producto');
    const montoRecibido = document.getElementById('monto-recibido');
    const metodoPago = document.getElementById('metodo-pago');

    if (select) {
        // Usar evento con captura para evitar conflictos
        select.addEventListener('change', seleccionarProducto, true);
        console.log('🔹 Evento change asignado al select');
    }

    if (montoRecibido) {
        // Usar evento input con manejo específico
        montoRecibido.addEventListener('input', function(e) {
            console.log('⌨️ Input en monto recibido:', e.target.value);
            // Solo calcular cambio, NO actualizar carrito
            calcularCambio();
        });
        console.log('🔹 Evento input asignado al monto recibido');
    }

    if (metodoPago) {
        metodoPago.addEventListener('change', function() {
            console.log('🔄 Método de pago cambiado:', this.value);
            if (this.value === 'tarjeta' && montoRecibido) {
                montoRecibido.value = '';
            }
            calcularCambio();
        });
    }

    // Inicializar una sola vez
    actualizarCarrito();
    calcularCambio();
    
    console.log('🎯 TPV completamente inicializado');
});

// =======================================================
// 8. VERIFICACIÓN DEL HTML
// =======================================================
function verificarHTML() {
    console.log('🔍 VERIFICANDO HTML...');
    
    const cuerpo = document.getElementById('cuerpo-carrito');
    console.log('Cuerpo carrito:', cuerpo);
    console.log('HTML del cuerpo:', cuerpo ? cuerpo.innerHTML : 'NO ENCONTRADO');
    console.log('Padre del cuerpo:', cuerpo ? cuerpo.parentElement : 'NO ENCONTRADO');
    
    // Verificar la tabla completa
    const tabla = document.querySelector('.tabla-carrito');
    console.log('Tabla completa:', tabla);
    console.log('HTML de la tabla:', tabla ? tabla.outerHTML : 'NO ENCONTRADA');
}

// Ejecutar verificación después de 2 segundos
setTimeout(() => {
    verificarHTML();
}, 2000);
</script>
@endsection