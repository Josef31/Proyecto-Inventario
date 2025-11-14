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
            <button class="btn-listo" id="btn-asignar-tpv">Asignar Cliente</button>
            <div id="cliente-info-tpv" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none;">
                <strong>Cliente asignado:</strong>
                <div id="cliente-texto-tpv"></div>
            </div>
        </div>
    </aside>

    <main class="seccion-ventas">
        <div class="cabecera-ventas">
            <h2>Punto de Venta (TPV)</h2>
        </div>

        <div class="tpv-grid">
            <div class="tpv-col-izquierda">

                <div class="buscador-productos">
                    <select id="producto-select-tpv">
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
                        <tbody id="carrito-body-tpv">
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999;">No hay productos en el carrito</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tpv-col-derecha">

                <div class="resumen-pago">
                    <div class="detalle-pago">
                        <p>Subtotal:</p>
                        <p id="subtotal-tpv">$0.00</p>
                    </div>
                    <div class="detalle-pago">
                        <p>Impuestos (IVA 16%):</p>
                        <p id="impuestos-tpv">$0.00</p>
                    </div>
                    <div class="detalle-pago total-final">
                        <p>TOTAL A PAGAR:</p>
                        <p class="valor-total" id="total-tpv">$0.00</p>
                    </div>
                </div>

                <div class="opciones-pago">
                    <h4>Método de Pago</h4>
                    <select id="pago-metodo-tpv">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                    </select>
                    <input type="number" id="monto-tpv" placeholder="Monto Recibido" min="0" step="0.01">

                    <div class="detalle-pago">
                        <p>Su Cambio:</p>
                        <p id="cambio-tpv">$0.00</p>
                    </div>
                </div>

                <div class="botones-acciones-tpv">
                    <button class="btn-accion btn-cobrar" id="btn-cobrar-tpv">COBRAR VENTA</button>
                    <button class="btn-accion btn-cancelar" id="btn-cancelar-tpv">CANCELAR</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// =======================================================
// TPV - SISTEMA AISLADO (No conflictos con JS global)
// =======================================================

// Namespace para evitar conflictos
const TPV = {
    // Variables privadas
    carrito: [],
    cliente: null,
    
    // Inicialización
    init: function() {
        console.log('🔄 Inicializando TPV aislado...');
        
        // Asignar eventos con namespaces únicos
        this.agregarEventos();
        this.actualizarVistaCarrito();
        this.calcularCambio();
        
        console.log('✅ TPV aislado listo');
    },
    
    // Agregar todos los eventos
    agregarEventos: function() {
        // Productos
        document.getElementById('producto-select-tpv').addEventListener('change', () => this.agregarProducto());
        
        // Cliente
        document.getElementById('btn-asignar-tpv').addEventListener('click', () => this.asignarCliente());
        
        // Ventas
        document.getElementById('btn-cobrar-tpv').addEventListener('click', () => this.procesarVenta());
        document.getElementById('btn-cancelar-tpv').addEventListener('click', () => this.limpiarTodo());
        
        // Pago
        document.getElementById('monto-tpv').addEventListener('input', () => this.calcularCambio());
        document.getElementById('pago-metodo-tpv').addEventListener('change', () => this.calcularCambio());
    },
    
    // 1. AGREGAR PRODUCTO AL CARRITO
    agregarProducto: function() {
        console.log('🛍️ TPV: Agregando producto...');
        
        const select = document.getElementById('producto-select-tpv');
        const opcion = select.options[select.selectedIndex];
        
        if (!opcion.value) {
            console.log('❌ TPV: No se seleccionó producto');
            return;
        }
        
        const productoId = parseInt(opcion.value);
        const productoNombre = opcion.getAttribute('data-name');
        const productoPrecio = parseFloat(opcion.getAttribute('data-price'));
        const productoStock = parseInt(opcion.getAttribute('data-stock'));
        
        console.log('📦 TPV Producto:', productoNombre, productoPrecio);
        
        // Buscar si ya está en el carrito
        let productoExistente = null;
        for (let i = 0; i < this.carrito.length; i++) {
            if (this.carrito[i].id === productoId) {
                productoExistente = this.carrito[i];
                break;
            }
        }
        
        if (productoExistente) {
            if (productoExistente.cantidad < productoStock) {
                productoExistente.cantidad++;
                console.log('➕ TPV: Cantidad aumentada:', productoExistente.cantidad);
            } else {
                alert('No hay suficiente stock');
                return;
            }
        } else {
            this.carrito.push({
                id: productoId,
                nombre: productoNombre,
                precio: productoPrecio,
                cantidad: 1,
                stock: productoStock
            });
            console.log('🆕 TPV: Nuevo producto agregado');
        }
        
        select.selectedIndex = 0;
        this.actualizarVistaCarrito();
    },
    
    // 2. ACTUALIZAR VISTA DEL CARRITO
    actualizarVistaCarrito: function() {
        console.log('🔄 TPV: Actualizando carrito, productos:', this.carrito.length);
        
        const cuerpo = document.getElementById('carrito-body-tpv');
        let subtotal = 0;
        
        cuerpo.innerHTML = '';
        
        if (this.carrito.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #999;">No hay productos en el carrito</td></tr>';
            console.log('🛒 TPV: Carrito vacío');
        } else {
            console.log('🎨 TPV: Dibujando productos en tabla');
            
            for (let i = 0; i < this.carrito.length; i++) {
                const producto = this.carrito[i];
                const subtotalProducto = producto.precio * producto.cantidad;
                subtotal += subtotalProducto;
                
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${producto.nombre}</td>
                    <td>
                        <input type="number" value="${producto.cantidad}" min="1" max="${producto.stock}" 
                               onchange="TPV.cambiarCantidad(${producto.id}, this.value)"
                               style="width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
                    </td>
                    <td>$${producto.precio.toFixed(2)}</td>
                    <td>$${subtotalProducto.toFixed(2)}</td>
                    <td>
                        <button onclick="TPV.quitarProducto(${producto.id})" 
                                style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                            ×
                        </button>
                    </td>
                `;
                cuerpo.appendChild(fila);
            }
            
            console.log('💰 TPV: Subtotal calculado:', subtotal);
        }
        
        this.actualizarTotales(subtotal);
    },
    
    // 3. ACTUALIZAR TOTALES
    actualizarTotales: function(subtotal) {
        const impuestos = subtotal * 0.16;
        const total = subtotal + impuestos;
        
        document.getElementById('subtotal-tpv').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('impuestos-tpv').textContent = `$${impuestos.toFixed(2)}`;
        document.getElementById('total-tpv').textContent = `$${total.toFixed(2)}`;
        
        this.calcularCambio();
    },
    
    // 4. CALCULAR CAMBIO
    calcularCambio: function() {
        const totalTexto = document.getElementById('total-tpv').textContent.replace('$', '');
        const total = parseFloat(totalTexto) || 0;
        const montoRecibido = parseFloat(document.getElementById('monto-tpv').value) || 0;
        const metodoPago = document.getElementById('pago-metodo-tpv').value;
        const btnCobrar = document.getElementById('btn-cobrar-tpv');
        
        console.log('💰 TPV Calculando cambio - Total:', total, 'Monto:', montoRecibido);
        
        let cambio = 0;
        let valido = false;
        
        if (metodoPago === 'efectivo') {
            cambio = montoRecibido - total;
            valido = montoRecibido >= total && total > 0;
        } else {
            cambio = 0;
            valido = total > 0;
        }
        
        document.getElementById('cambio-tpv').textContent = `$${Math.max(0, cambio).toFixed(2)}`;
        
        if (valido && this.carrito.length > 0) {
            btnCobrar.disabled = false;
            btnCobrar.style.backgroundColor = '#2ecc71';
            console.log('✅ TPV: Botón cobrar HABILITADO');
        } else {
            btnCobrar.disabled = true;
            btnCobrar.style.backgroundColor = '#cccccc';
            console.log('❌ TPV: Botón cobrar DESHABILITADO');
        }
    },
    
    // 5. ASIGNAR CLIENTE
    asignarCliente: function() {
        console.log('👤 TPV: Asignando cliente...');
        
        const nombre = document.getElementById('cliente-nombre').value.trim();
        const rfc = document.getElementById('cliente-rfc').value.trim();
        
        console.log('👤 TPV Datos cliente:', nombre, rfc);
        
        if (!nombre) {
            alert('Por favor ingresa el nombre del cliente');
            return;
        }
        
        this.cliente = {
            nombre: nombre,
            rfc: rfc || 'No especificado'
        };
        
        document.getElementById('cliente-texto-tpv').innerHTML = `Nombre: ${nombre}<br>RFC: ${rfc || 'No especificado'}`;
        document.getElementById('cliente-info-tpv').style.display = 'block';
        
        alert(`✅ Cliente asignado:\nNombre: ${nombre}\nRFC: ${rfc || 'No especificado'}`);
        console.log('✅ TPV: Cliente asignado correctamente');
    },
    
    // 6. PROCESAR VENTA
    procesarVenta: function() {
        console.log('💰 TPV: PROCESANDO VENTA...');
        console.log('📦 TPV Carrito:', this.carrito);
        console.log('🔢 TPV Productos en carrito:', this.carrito.length);
        
        if (this.carrito.length === 0) {
            alert('❌ El carrito está vacío. Agrega productos para cobrar.');
            console.error('❌ TPV VENTA FALLIDA: Carrito vacío');
            return;
        }
        
        console.log('✅ TPV: Carrito OK, continuando...');
        
        const totalTexto = document.getElementById('total-tpv').textContent.replace('$', '');
        const total = parseFloat(totalTexto);
        const metodoPago = document.getElementById('pago-metodo-tpv').value;
        const montoRecibido = parseFloat(document.getElementById('monto-tpv').value) || 0;
        
        console.log('💳 TPV Datos pago:', { total, metodoPago, montoRecibido });
        
        if (metodoPago === 'efectivo' && montoRecibido < total) {
            alert('El monto recibido es menor al total a pagar.');
            return;
        }
        
        const datosVenta = {
            items: this.carrito.map(producto => ({
                product_id: producto.id,
                name: producto.nombre,
                price: producto.precio,
                quantity: producto.cantidad
            })),
            customer_name: this.cliente ? this.cliente.nombre : '',
            customer_rfc: this.cliente ? this.cliente.rfc : '',
            payment_method: metodoPago,
            amount_received: montoRecibido,
            total: total
        };
        
        console.log('📤 TPV Enviando datos:', datosVenta);
        
        try {
            fetch('/sales/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(datosVenta)
            })
            .then(response => response.json())
            .then(resultado => {
                console.log('📥 TPV Respuesta servidor:', resultado);
                
                if (resultado.success) {
                    alert(`✅ Venta ${resultado.sale_code} procesada\nTotal: $${resultado.total.toFixed(2)}`);
                    this.limpiarTodo();
                } else {
                    alert('❌ Error: ' + (resultado.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('TPV Error:', error);
                alert('❌ Error de conexión');
            });
        } catch (error) {
            console.error('TPV Error:', error);
            alert('❌ Error al procesar la venta');
        }
    },
    
    // 7. FUNCIONES AUXILIARES
    cambiarCantidad: function(id, nuevaCantidad) {
        const cantidad = parseInt(nuevaCantidad);
        
        for (let i = 0; i < this.carrito.length; i++) {
            if (this.carrito[i].id === id) {
                if (cantidad < 1 || cantidad > this.carrito[i].stock) {
                    alert(`Cantidad debe ser entre 1 y ${this.carrito[i].stock}`);
                    this.actualizarVistaCarrito();
                    return;
                }
                this.carrito[i].cantidad = cantidad;
                break;
            }
        }
        
        this.actualizarVistaCarrito();
    },
    
    quitarProducto: function(id) {
        if (confirm('¿Eliminar producto del carrito?')) {
            this.carrito = this.carrito.filter(producto => producto.id !== id);
            this.actualizarVistaCarrito();
        }
    },
    
    limpiarTodo: function() {
        if (this.carrito.length > 0 && !confirm('¿Cancelar venta y vaciar carrito?')) {
            return;
        }
        
        this.carrito = [];
        this.cliente = null;
        document.getElementById('producto-select-tpv').selectedIndex = 0;
        document.getElementById('monto-tpv').value = '';
        document.getElementById('cliente-nombre').value = '';
        document.getElementById('cliente-rfc').value = '';
        document.getElementById('cliente-info-tpv').style.display = 'none';
        
        this.actualizarVistaCarrito();
    },
    
    // Debug
    debug: function() {
        console.log('🐛 TPV DEBUG:');
        console.log('Carrito:', this.carrito);
        console.log('Cliente:', this.cliente);
        console.log('Productos en carrito:', this.carrito.length);
    }
};

// Inicializar TPV cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    TPV.init();
});
</script>

<style>
.btn-cobrar:disabled {
    background-color: #cccccc !important;
    cursor: not-allowed;
}

.tabla-carrito {
    width: 100%;
    border-collapse: collapse;
}

.tabla-carrito th,
.tabla-carrito td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.tabla-carrito th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.detalle-pago {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 8px 0;
}

.total-final {
    border-top: 2px solid #333;
    font-weight: bold;
    font-size: 1.1em;
}

.valor-total {
    color: #e74c3c;
    font-weight: bold;
}

.btn-listo {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    margin-top: 10px;
}

.btn-listo:hover {
    background-color: #2980b9;
}

.btn-accion {
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    width: 100%;
    margin-bottom: 10px;
}

.btn-cobrar {
    background-color: #2ecc71;
    color: white;
}

.btn-cancelar {
    background-color: #e74c3c;
    color: white;
}
</style>
@endsection