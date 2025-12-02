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
            <p class="rol-usuario">{{ auth()->user()->role ? auth()->user()->role->name : 'Administrador' }}</p>
        </div>
        <div class="seccion-formulario">
            <h3>DETALLES DE CLIENTE</h3>
            <label for="cliente-select">Seleccionar Cliente:</label>
            <select id="cliente-select">
                <option value="">Cliente General</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-rfc="{{ $customer->rfc }}">
                        {{ $customer->name }}{{ $customer->rfc ? ' (' . $customer->rfc . ')' : '' }}
                    </option>
                @endforeach
            </select>
            <button class="btn-listo" id="btn-asignar-tpv">Asignar Cliente</button>
            <div id="cliente-info-tpv" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none !important; color: #333;">
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

                <div class="buscador-productos" style="margin-top: 15px;">
                    <select id="servicio-select-tpv">
                        <option value="" disabled selected>--- Seleccionar Servicio ---</option>
                        @foreach ($services as $service)
                        <option
                            value="{{ $service['id'] }}"
                            data-name="{{ $service['name'] }}"
                            data-price="{{ $service['price'] }}">
                            ID: {{ $service['id'] }} - {{ $service['name'] }} (${{ $service['price'] }})
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
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
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
        
        // Servicios
        document.getElementById('servicio-select-tpv').addEventListener('change', () => this.agregarServicio());
        
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
            if (this.carrito[i].tipo === 'producto' && this.carrito[i].id === productoId) {
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
                tipo: 'producto',
                id: productoId,
                nombre: productoNombre,
                precio: productoPrecio,
                cantidad: 1,
                stock: productoStock
            });
            console.log('🆕 TPV: Nuevo producto agregado');
        }
        
        // Deshabilitar servicios si hay productos
        document.getElementById('servicio-select-tpv').disabled = true;
        
        select.selectedIndex = 0;
        this.actualizarVistaCarrito();
    },
    
    // 1B. AGREGAR SERVICIO AL CARRITO
    agregarServicio: function() {
        console.log('🛍️ TPV: Agregando servicio...');
        
        const select = document.getElementById('servicio-select-tpv');
        const opcion = select.options[select.selectedIndex];
        
        if (!opcion.value) {
            console.log('❌ TPV: No se seleccionó servicio');
            return;
        }
        
        const servicioId = parseInt(opcion.value);
        const servicioNombre = opcion.getAttribute('data-name');
        const servicioPrecio = parseFloat(opcion.getAttribute('data-price'));
        
        console.log('🔧 TPV Servicio:', servicioNombre, servicioPrecio);
        
        // Buscar si ya está en el carrito
        let servicioExistente = null;
        for (let i = 0; i < this.carrito.length; i++) {
            if (this.carrito[i].tipo === 'servicio' && this.carrito[i].id === servicioId) {
                servicioExistente = this.carrito[i];
                break;
            }
        }
        
        if (servicioExistente) {
            alert('Este servicio ya está en el carrito');
            return;
        } else {
            this.carrito.push({
                tipo: 'servicio',
                id: servicioId,
                nombre: servicioNombre,
                precio: servicioPrecio,
                cantidad: 1
            });
            console.log('🆕 TPV: Nuevo servicio agregado');
        }
        
        // Deshabilitar productos si hay servicios
        document.getElementById('producto-select-tpv').disabled = true;
        
        select.selectedIndex = 0;
        this.actualizarVistaCarrito();
    },
    
    // 2. ACTUALIZAR VISTA DEL CARRITO
    actualizarVistaCarrito: function() {
        console.log('🔄 TPV: Actualizando carrito, items:', this.carrito.length);
        
        const cuerpo = document.getElementById('carrito-body-tpv');
        let subtotal = 0;
        
        cuerpo.innerHTML = '';
        
        if (this.carrito.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #999;">No hay productos/servicios en el carrito</td></tr>';
            console.log('🛒 TPV: Carrito vacío');
            // Habilitar ambos selectores cuando el carrito está vacío
            document.getElementById('producto-select-tpv').disabled = false;
            document.getElementById('servicio-select-tpv').disabled = false;
        } else {
            console.log('🎨 TPV: Dibujando items en tabla');
            
            for (let i = 0; i < this.carrito.length; i++) {
                const item = this.carrito[i];
                const subtotalItem = item.precio * item.cantidad;
                subtotal += subtotalItem;
                
                const fila = document.createElement('tr');
                
                if (item.tipo === 'producto') {
                    fila.innerHTML = `
                        <td>${item.nombre}</td>
                        <td>
                            <input type="number" value="${item.cantidad}" min="1" max="${item.stock}" 
                                   onchange="TPV.cambiarCantidad(${item.id}, '${item.tipo}', this.value)"
                                   style="width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
                        </td>
                        <td>$${item.precio.toFixed(2)}</td>
                        <td>$${subtotalItem.toFixed(2)}</td>
                        <td>
                            <button onclick="TPV.quitarItem(${item.id}, '${item.tipo}')" 
                                    style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                                ×
                            </button>
                        </td>
                    `;
                } else { // servicio
                    fila.innerHTML = `
                        <td>${item.nombre} <span style="color: #3498db; font-size: 0.85em;">(Servicio)</span></td>
                        <td>1</td>
                        <td>$${item.precio.toFixed(2)}</td>
                        <td>$${subtotalItem.toFixed(2)}</td>
                        <td>
                            <button onclick="TPV.quitarItem(${item.id}, '${item.tipo}')" 
                                    style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                                ×
                            </button>
                        </td>
                    `;
                }
                
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
        
        // ID 1 = Efectivo, ID 5 = Dólares (también requieren monto)
        const metodoPagoId = parseInt(metodoPago);
        if (metodoPagoId === 1 || metodoPagoId === 5) {
            // Métodos que requieren monto recibido
            if (montoRecibido >= total) {
                cambio = montoRecibido - total;
                valido = true;
            }
        } else {
            // Otros métodos de pago (tarjeta, transferencia)
            valido = true;
        }
        
        document.getElementById('cambio-tpv').textContent = `$${cambio.toFixed(2)}`;
        
        // Habilitar/deshabilitar botón cobrar
        if (valido && this.carrito.length > 0) {
            btnCobrar.disabled = false;
            btnCobrar.style.backgroundColor = '#28a745';
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
        
        const select = document.getElementById('cliente-select');
        const opcion = select.options[select.selectedIndex];
        
        if (!opcion.value) {
            // Cliente General
            this.cliente = {
                id: null,
                nombre: 'Cliente General',
                rfc: ''
            };
        } else {
            this.cliente = {
                id: parseInt(opcion.value),
                nombre: opcion.getAttribute('data-name'),
                rfc: opcion.getAttribute('data-rfc') || ''
            };
        }
        
        document.getElementById('cliente-texto-tpv').innerHTML = `Nombre: ${this.cliente.nombre}<br>RFC: ${this.cliente.rfc || 'No especificado'}`;
        const clienteInfoDiv = document.getElementById('cliente-info-tpv');
        clienteInfoDiv.style.display = 'block';
        clienteInfoDiv.style.setProperty('display', 'block', 'important');
        
        alert(`✅ Cliente asignado:\nNombre: ${this.cliente.nombre}\nRFC: ${this.cliente.rfc || 'No especificado'}`);
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
        
        // Separar productos y servicios
        const productos = this.carrito.filter(item => item.tipo === 'producto');
        const servicios = this.carrito.filter(item => item.tipo === 'servicio');
        
        const datosVenta = {
            items: productos.length > 0 ? productos.map(producto => ({
                product_id: producto.id,
                name: producto.nombre,
                price: producto.precio,
                quantity: producto.cantidad
            })) : [],
            services: servicios.length > 0 ? servicios.map(servicio => ({
                service_id: servicio.id,
                name: servicio.nombre,
                price: servicio.precio
            })) : [],
            customer_id: this.cliente ? this.cliente.id : null,
            payment_method_id: parseInt(metodoPago),
            payment_currency: parseInt(metodoPago) === 1 ? 'USD' : 'Bs', // ID 1 = Dólares
            amount_received: montoRecibido,
            total: total
        };
        
        console.log('📤 TPV: Enviando datos de venta:', datosVenta);
        
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
                    alert(`✅ Venta ${resultado.sale_code} procesada correctamente\nFactura: ${resultado.invoice_number}\nCambio: $${resultado.change.toFixed(2)}`);
                    this.limpiarTodo();
                } else if (resultado.error) {
                    alert('❌ Error: ' + resultado.error);
                } else {
                    alert('❌ Error: ' + (resultado.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('TPV Error completo:', error);
                alert('❌ Error de conexión: ' + error.message);
            });
        } catch (error) {
            console.error('TPV Error:', error);
            alert('❌ Error al procesar la venta');
        }
    },
    
    // 7. FUNCIONES AUXILIARES
    cambiarCantidad: function(id, tipo, nuevaCantidad) {
        const cantidad = parseInt(nuevaCantidad);
        
        for (let i = 0; i < this.carrito.length; i++) {
            if (this.carrito[i].id === id && this.carrito[i].tipo === tipo) {
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
    
    
    quitarItem: function(id, tipo) {
        if (confirm('¿Eliminar este item del carrito?')) {
            this.carrito = this.carrito.filter(item => !(item.id === id && item.tipo === tipo));
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