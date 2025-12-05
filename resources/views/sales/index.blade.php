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
            <label for="cliente-rfc-input">RFC del Cliente:</label>
            <input type="text" id="cliente-rfc-input" placeholder="Ingrese RFC del cliente" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
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
                    <input type="text" id="producto-search-input" placeholder="Buscar producto..." style="width: 100%; padding: 8px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <select id="producto-select-tpv" size="5" style="width: 100%; min-height: 150px;">
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
                    <input type="text" id="servicio-search-input" placeholder="Buscar servicio..." style="width: 100%; padding: 8px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <select id="servicio-select-tpv" size="5" style="width: 100%; min-height: 150px;">
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
                            <option value="{{ $method->id }}" data-currency="{{ $method->currency }}">{{ $method->name }}</option>
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
    customersData: @json($customers), // Datos de clientes para búsqueda local
    exchangeRate: {{ $exchangeRate }}, // Tasa de cambio actual
    currentCurrency: 'USD', // Moneda actual (por defecto USD)
    
    // Funciones de conversión de moneda
    convertPrice: function(priceUSD, toCurrency) {
        if (toCurrency === 'Bs') {
            return priceUSD * this.exchangeRate;
        }
        return priceUSD; // Ya está en USD
    },
    
    getCurrencySymbol: function(currency) {
        return currency === 'Bs' ? 'Bs' : '$';
    },
    
    formatPrice: function(priceUSD) {
        const convertedPrice = this.convertPrice(priceUSD, this.currentCurrency);
        const symbol = this.getCurrencySymbol(this.currentCurrency);
        return `${symbol}${convertedPrice.toFixed(2)}`;
    },
    
    // Inicialización
    init: function() {
        console.log('🔄 Inicializando TPV aislado...');
        console.log('💱 Tasa de cambio:', this.exchangeRate);
        
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
        document.getElementById('pago-metodo-tpv').addEventListener('change', () => this.onPaymentMethodChange());
        
        // Búsqueda de productos
        document.getElementById('producto-search-input').addEventListener('input', (e) => this.filtrarSelect('producto-select-tpv', e.target.value));
        
        // Búsqueda de servicios
        document.getElementById('servicio-search-input').addEventListener('input', (e) => this.filtrarSelect('servicio-select-tpv', e.target.value));
    },
    
    // Filtrar opciones del select basado en búsqueda
    filtrarSelect: function(selectId, searchTerm) {
        const select = document.getElementById(selectId);
        const options = select.getElementsByTagName('option');
        const searchLower = searchTerm.toLowerCase();
        
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === '') continue; // Skip placeholder
            
            const text = option.textContent.toLowerCase();
            if (text.includes(searchLower)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
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
                Toastify({
                    text: "⚠️ No hay suficiente stock",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f39c12"
                }).showToast();
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
            servicioExistente.cantidad++;
            console.log('➕ TPV: Cantidad de servicio incrementada a', servicioExistente.cantidad);
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
                
                // Precios formateados
                const precioFormateado = this.formatPrice(item.precio);
                const subtotalFormateado = this.formatPrice(subtotalItem);
                
                if (item.tipo === 'producto') {
                    fila.innerHTML = `
                        <td>${item.nombre}</td>
                        <td>
                            <input type="number" value="${item.cantidad}" min="1" max="${item.stock}" 
                                   onchange="TPV.cambiarCantidad(${item.id}, '${item.tipo}', this.value)"
                                   style="width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
                        </td>
                        <td>${precioFormateado}</td>
                        <td>${subtotalFormateado}</td>
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
                        <td>
                            <input type="number" value="${item.cantidad}" min="1" 
                                   onchange="TPV.cambiarCantidad(${item.id}, '${item.tipo}', this.value)"
                                   style="width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
                        </td>
                        <td>${precioFormateado}</td>
                        <td>${subtotalFormateado}</td>
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
        
        document.getElementById('subtotal-tpv').textContent = this.formatPrice(subtotal);
        document.getElementById('impuestos-tpv').textContent = this.formatPrice(impuestos);
        document.getElementById('total-tpv').textContent = this.formatPrice(total);
        
        this.calcularCambio();
    },
    
    // 4. CALCULAR CAMBIO
    calcularCambio: function() {
        // Obtener total numérico limpio (sin símbolo de moneda)
        const totalTexto = document.getElementById('total-tpv').textContent;
        const symbol = this.getCurrencySymbol(this.currentCurrency);
        const total = parseFloat(totalTexto.replace(symbol, '')) || 0;
        
        const montoRecibido = parseFloat(document.getElementById('monto-tpv').value) || 0;
        const metodoPago = document.getElementById('pago-metodo-tpv').value;
        const btnCobrar = document.getElementById('btn-cobrar-tpv');
        
        console.log('💰 TPV Calculando cambio - Total:', total, 'Monto:', montoRecibido);
        
        let cambio = 0;
        let valido = false;
        
        // ID 1 = Dólares, ID 2 = Bolívares (Efectivo)
        // Asumimos que métodos de efectivo requieren cálculo de cambio
        const metodoPagoId = parseInt(metodoPago);
        
        // Lógica simplificada: si hay monto recibido, calcular cambio
        if (montoRecibido > 0) {
            if (montoRecibido >= total) {
                cambio = montoRecibido - total;
                valido = true;
            }
        } else {
            // Si no hay monto recibido, asumimos pago exacto para métodos no-efectivo
            // O requerimos monto para efectivo
            // Por ahora mantenemos lógica simple: si no es efectivo, es válido
            if (metodoPagoId !== 1 && metodoPagoId !== 2) {
                valido = true;
            }
        }
        
        // Mostrar cambio con el símbolo correcto (siempre en la misma moneda del pago)
        document.getElementById('cambio-tpv').textContent = `${symbol}${cambio.toFixed(2)}`;
        
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
    
    // Manejar cambio de método de pago
    onPaymentMethodChange: function() {
        const select = document.getElementById('pago-metodo-tpv');
        const selectedOption = select.options[select.selectedIndex];
        const newCurrency = selectedOption.dataset.currency;
        
        console.log('💳 Cambio de método de pago. Nueva moneda:', newCurrency);
        
        if (newCurrency && newCurrency !== this.currentCurrency) {
            this.currentCurrency = newCurrency;
            console.log('💱 Moneda cambiada a:', this.currentCurrency);
            
            // Actualizar toda la vista con la nueva moneda
            this.actualizarVistaCarrito();
        } else {
            // Solo recalcular cambio si la moneda no cambió
            this.calcularCambio();
        }
    },
    
    // 5. ASIGNAR CLIENTE
    // 5. ASIGNAR CLIENTE
    asignarCliente: function() {
        console.log('👤 TPV: Asignando cliente...');
        
        const rfcInput = document.getElementById('cliente-rfc-input').value.trim();
        
        if (!rfcInput) {
            // Cliente General (si no se escribe nada)
            this.cliente = {
                id: null,
                nombre: 'Cliente General',
                rfc: ''
            };
            this.mostrarClienteAsignado();
            return;
        }
        
        // Buscar cliente por RFC
        const clienteEncontrado = this.customersData.find(c => c.rfc && c.rfc.toLowerCase() === rfcInput.toLowerCase());
        
        if (clienteEncontrado) {
            this.cliente = {
                id: clienteEncontrado.id,
                nombre: clienteEncontrado.name,
                rfc: clienteEncontrado.rfc
            };
            this.mostrarClienteAsignado();
        } else {
            // Cliente no encontrado
            Swal.fire({
                icon: 'error',
                title: 'Cliente no encontrado',
                text: `No se encontró ningún cliente con el RFC: ${rfcInput}`,
                confirmButtonColor: '#e74c3c'
            });
        }
    },
    
    mostrarClienteAsignado: function() {
        document.getElementById('cliente-texto-tpv').innerHTML = `Nombre: ${this.cliente.nombre}<br>RFC: ${this.cliente.rfc || 'No especificado'}`;
        const clienteInfoDiv = document.getElementById('cliente-info-tpv');
        clienteInfoDiv.style.display = 'block';
        clienteInfoDiv.style.setProperty('display', 'block', 'important');
        
        Toastify({
            text: `✅ Cliente: ${this.cliente.nombre}`,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#27ae60"
        }).showToast();
        console.log('✅ TPV: Cliente asignado correctamente');
    },
    
    // 6. PROCESAR VENTA
    procesarVenta: function() {
        console.log('💰 TPV: PROCESANDO VENTA...');
        console.log('📦 TPV Carrito:', this.carrito);
        console.log('🔄 TPV: Iniciando proceso de venta...');
        
        // Validar que hay una caja abierta
        const cajaAbierta = @json($openCashRegister !== null);
        if (!cajaAbierta) {
            Swal.fire({
                icon: 'warning',
                title: 'Caja no abierta',
                text: 'Debe abrir una caja antes de realizar ventas.',
                confirmButtonColor: '#3498db'
            });
            return;
        }
        
        if (this.carrito.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Carrito vacío',
                text: 'Agrega productos o servicios para cobrar.',
                confirmButtonColor: '#3498db'
            });
            console.error('❌ TPV VENTA FALLIDA: Carrito vacío');
            return;
        }
        
        console.log('✅ TPV: Carrito OK, continuando...');
        
        const totalTexto = document.getElementById('total-tpv').textContent.replace(/[$Bs]/g, '').trim();
        const total = parseFloat(totalTexto);
        const metodoPago = document.getElementById('pago-metodo-tpv').value; // Keep original ID
        const montoRecibido = parseFloat(document.getElementById('monto-tpv').value) || 0;
        
        console.log('💳 TPV Datos pago:', { total, metodoPago, montoRecibido });
        
        if (!metodoPago) { // Added check for payment method
            Toastify({
                text: "⚠️ Selecciona un método de pago",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#f39c12"
            }).showToast();
            return;
        }
        if (metodoPago === 'efectivo' && montoRecibido < total) {
            Swal.fire({
                icon: 'warning',
                title: 'Monto insuficiente',
                text: 'El monto recibido es menor al total a pagar.',
                confirmButtonColor: '#3498db'
            });
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
                price: servicio.precio,
                quantity: servicio.cantidad
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
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(datosVenta)
            })
            .then(response => {
                console.log('📥 TPV Response status:', response.status);
                
                // Check if response is ok (status 200-299)
                if (!response.ok) {
                    // Try to parse as JSON first
                    return response.text().then(text => {
                        console.log('📥 TPV Error response:', text);
                        
                        try {
                            const data = JSON.parse(text);
                            
                            // Handle validation errors (422)
                            if (response.status === 422 && data.errors) {
                                throw { validation: true, errors: data.errors, message: data.message };
                            }
                            
                            // Handle other errors with error field
                            if (data.error) {
                                throw new Error(data.error);
                            }
                            
                            throw new Error(data.message || 'Error en el servidor');
                        } catch (parseError) {
                            // If not JSON, throw generic error
                            if (parseError.validation) throw parseError;
                            throw new Error('Error en el servidor: ' + text.substring(0, 100));
                        }
                    });
                }
                return response.json();
            })
            .then(resultado => {
                console.log('📥 TPV Respuesta servidor:', resultado);
                
                if (resultado.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Venta exitosa!',
                        html: `<strong>Venta:</strong> ${resultado.sale_code}<br><strong>Factura:</strong> ${resultado.invoice_number}<br><strong>Cambio:</strong> $${resultado.change.toFixed(2)}`,
                        confirmButtonColor: '#27ae60'
                    });
                    this.limpiarTodo(true); // Skip confirmation after successful sale
                } else if (resultado.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resultado.error,
                        confirmButtonColor: '#e74c3c'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resultado.message || 'Error desconocido',
                        confirmButtonColor: '#e74c3c'
                    });
                }
            })
            .catch(error => {
                console.error('TPV Error completo:', error);
                
                // Handle validation errors with friendly messages
                if (error.validation && error.errors) {
                    let mensajeError = '⚠️ Por favor corrige lo siguiente:\n\n';
                    
                    // Get first error message from each field
                    for (let campo in error.errors) {
                        mensajeError += '• ' + error.errors[campo][0] + '\n';
                    }
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validación',
                        html: mensajeError.replace(/\n/g, '<br>'),
                        confirmButtonColor: '#f39c12'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Error desconocido',
                        confirmButtonColor: '#e74c3c'
                    });
                }
            });
        } catch (error) {
            console.error('TPV Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la venta',
                confirmButtonColor: '#e74c3c'
            });
        }
    },
    
    // 7. FUNCIONES AUXILIARES
    cambiarCantidad: function(id, tipo, nuevaCantidad) {
        const cantidad = parseInt(nuevaCantidad);
        
        for (let i = 0; i < this.carrito.length; i++) {
            if (this.carrito[i].id === id && this.carrito[i].tipo === tipo) {
                // Validar cantidad mínima
                if (cantidad < 1) {
                    Toastify({
                        text: "⚠️ La cantidad debe ser al menos 1",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#f39c12"
                    }).showToast();
                    this.actualizarVistaCarrito();
                    return;
                }
                
                // Para productos, validar stock
                if (tipo === 'producto') {
                    if (cantidad > this.carrito[i].stock) {
                        Toastify({
                            text: `⚠️ Stock disponible: ${this.carrito[i].stock}`,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f39c12"
                        }).showToast();
                        this.actualizarVistaCarrito();
                        return;
                    }
                }
                
                // Actualizar cantidad
                this.carrito[i].cantidad = cantidad;
                break;
            }
        }
        
        this.actualizarVistaCarrito();
    },
    
    
    quitarItem: function(id, tipo) {
        Swal.fire({
            title: '¿Eliminar item?',
            text: '¿Deseas eliminar este item del carrito?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.carrito = this.carrito.filter(item => !(item.id === id && item.tipo === tipo));
                this.actualizarVistaCarrito();
                Toastify({
                    text: "🗑️ Item eliminado",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c"
                }).showToast();
            }
        });
    },
    
    limpiarTodo: function(skipConfirm = false) {
        if (!skipConfirm && this.carrito.length > 0) {
            Swal.fire({
                title: '¿Cancelar venta?',
                text: '¿Deseas vaciar el carrito y cancelar la venta?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.ejecutarLimpieza();
                }
            });
            return;
        }
        
        this.ejecutarLimpieza();
    },
    
    ejecutarLimpieza: function() {
        this.carrito = [];
        this.cliente = null;
        
        // Reset selects
        document.getElementById('producto-select-tpv').selectedIndex = 0;
        document.getElementById('servicio-select-tpv').selectedIndex = 0;
        document.getElementById('cliente-rfc-input').value = '';
        
        // Reset payment fields
        document.getElementById('monto-tpv').value = '';
        
        // Hide customer info
        document.getElementById('cliente-info-tpv').style.display = 'none';
        document.getElementById('cliente-texto-tpv').innerHTML = '';
        
        // Re-enable both selects
        document.getElementById('producto-select-tpv').disabled = false;
        document.getElementById('servicio-select-tpv').disabled = false;
        
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