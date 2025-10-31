@extends('layouts.app')

@section('title', 'Ventas | Sistema de Administración')

@section('content')
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil"></div>
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
                    <input type="text" id="input-busqueda" placeholder="Buscar producto por nombre o código...">
                    <button class="btn-buscar" onclick="buscarProducto()">Buscar</button>
                </div>
                
                <div id="resultado-busqueda" class="lista-resultados">
                    <!-- Resultados de búsqueda aparecerán aquí -->
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo-carrito">
                            <!-- Items del carrito aparecerán aquí -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tpv-col-derecha">
                
                <div class="resumen-pago">
                    <div class="detalle-pago"> <p>Subtotal:</p> <p id="total-subtotal">0.00</p> </div>
                    <div class="detalle-pago"> <p>Impuestos (IVA 16%):</p> <p id="total-impuestos">0.00</p> </div>
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
                    
                    <div class="detalle-pago"> <p>Su Cambio:</p> <p id="monto-cambio">0.00</p> </div>
                </div>

                <div class="botones-acciones-tpv">
                    <button class="btn-accion btn-cobrar" onclick="finalizarVenta()">COBRAR VENTA</button>
                    <button class="btn-accion btn-cancelar" onclick="cancelarVenta()">CANCELAR</button>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script>
let carrito = [];

function buscarProducto() {
    const searchTerm = document.getElementById('input-busqueda').value;
    
    if (searchTerm.length < 2) {
        alert('Ingresa al menos 2 caracteres para buscar');
        return;
    }

    fetch(`/sales/search?search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(products => {
            const resultados = document.getElementById('resultado-busqueda');
            resultados.innerHTML = '';
            
            if (products.length === 0) {
                resultados.innerHTML = '<p>No se encontraron productos</p>';
                return;
            }

            products.forEach(product => {
                const div = document.createElement('div');
                div.className = 'item-resultado';
                div.innerHTML = `
                    <span>${product.name} - $${product.price} (Stock: ${product.stock})</span>
                    <button onclick="agregarAlCarrito(${product.id}, '${product.name}', ${product.price}, ${product.stock})">
                        Agregar
                    </button>
                `;
                resultados.appendChild(div);
            });
        })
        .catch(error => console.error('Error:', error));
}

function agregarAlCarrito(id, nombre, precio, stock) {
    const itemExistente = carrito.find(item => item.id === id);
    
    if (itemExistente) {
        if (itemExistente.cantidad < stock) {
            itemExistente.cantidad++;
        } else {
            alert('No hay suficiente stock disponible');
            return;
        }
    } else {
        carrito.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });
    }
    
    actualizarCarrito();
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(item => item.id !== id);
    actualizarCarrito();
}

function actualizarCarrito() {
    const cuerpoCarrito = document.getElementById('cuerpo-carrito');
    cuerpoCarrito.innerHTML = '';
    
    let subtotal = 0;
    
    carrito.forEach(item => {
        const itemSubtotal = item.precio * item.cantidad;
        subtotal += itemSubtotal;
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nombre}</td>
            <td>
                <input type="number" value="${item.cantidad}" min="1" 
                       onchange="actualizarCantidad(${item.id}, this.value)" 
                       style="width: 60px;">
            </td>
            <td>$${item.precio.toFixed(2)}</td>
            <td>$${itemSubtotal.toFixed(2)}</td>
            <td>
                <button onclick="eliminarDelCarrito(${item.id})" class="btn-eliminar">×</button>
            </td>
        `;
        cuerpoCarrito.appendChild(tr);
    });
    
    const impuestos = subtotal * 0.16;
    const total = subtotal + impuestos;
    
    document.getElementById('total-subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('total-impuestos').textContent = `$${impuestos.toFixed(2)}`;
    document.getElementById('total-pagar').textContent = `$${total.toFixed(2)}`;
    
    // Calcular cambio
    calcularCambio();
}

function actualizarCantidad(id, nuevaCantidad) {
    const item = carrito.find(item => item.id === id);
    if (item && nuevaCantidad > 0) {
        item.cantidad = parseInt(nuevaCantidad);
        actualizarCarrito();
    }
}

function calcularCambio() {
    const montoRecibido = parseFloat(document.getElementById('monto-recibido').value) || 0;
    const total = parseFloat(document.getElementById('total-pagar').textContent.replace('$', ''));
    const cambio = montoRecibido - total;
    
    document.getElementById('monto-cambio').textContent = `$${Math.max(0, cambio).toFixed(2)}`;
}

function finalizarVenta() {
    if (carrito.length === 0) {
        alert('El carrito está vacío');
        return;
    }
    
    const total = parseFloat(document.getElementById('total-pagar').textContent.replace('$', ''));
    const metodoPago = document.getElementById('metodo-pago').value;
    const montoRecibido = parseFloat(document.getElementById('monto-recibido').value) || 0;
    
    if (metodoPago === 'efectivo' && montoRecibido < total) {
        alert('El monto recibido es menor al total a pagar');
        return;
    }
    
    // Aquí iría la lógica para procesar la venta en el backend
    alert('Venta procesada exitosamente');
    cancelarVenta();
}

function cancelarVenta() {
    carrito = [];
    actualizarCarrito();
    document.getElementById('input-busqueda').value = '';
    document.getElementById('resultado-busqueda').innerHTML = '';
    document.getElementById('monto-recibido').value = '';
    document.getElementById('cliente-nombre').value = '';
    document.getElementById('cliente-rfc').value = '';
}

// Event listeners
document.getElementById('monto-recibido').addEventListener('input', calcularCambio);
document.getElementById('metodo-pago').addEventListener('change', function() {
    document.getElementById('monto-recibido').value = '';
    calcularCambio();
});
</script>
@endsection