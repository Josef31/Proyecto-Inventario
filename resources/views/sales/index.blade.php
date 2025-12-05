@extends('layouts.app')

@section('title', 'Ventas | Sistema de Administración')

<style>
    /* Estilos forzados para inputs de pago */
    #pago-metodo-tpv {
        padding: 12px !important;
        font-size: 15px !important;
        border: 2px solid #ddd !important;
        border-radius: 4px !important;
        background-color: white !important;
        height: 45px !important;
        line-height: normal !important;
        width: auto !important;
    }
    
    #monto-pago-tpv {
        padding: 12px !important;
        font-size: 15px !important;
        border: 2px solid #ddd !important;
        border-radius: 4px !important;
        height: 45px !important;
        line-height: normal !important;
        width: auto !important;
    }
    
    #btn-agregar-pago {
        padding: 12px 20px !important;
        font-size: 15px !important;
        font-weight: bold !important;
        height: 45px !important;
        line-height: normal !important;
    }
</style>

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
                    <h4>Métodos de Pago</h4>
                    
                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <select id="pago-metodo-tpv" style="flex: 1 !important; padding: 12px !important; font-size: 15px !important; border: 2px solid #ddd !important; border-radius: 4px !important; background-color: white !important; height: auto !important; min-height: 45px !important;">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" data-currency="{{ $method->currency }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" id="monto-pago-tpv" placeholder="Monto" min="0.01" step="0.01" style="flex: 1 !important; padding: 12px !important; font-size: 15px !important; border: 2px solid #ddd !important; border-radius: 4px !important; height: auto !important; min-height: 45px !important;">
                        <button class="btn-accion" id="btn-agregar-pago" style="padding: 12px 20px !important; background-color: #039438 !important; font-size: 15px !important; font-weight: bold !important; height: auto !important; min-height: 45px !important;">+ AGREGAR</button>
                    </div>

                    <div id="pagos-lista" style="margin-bottom: 15px;">
                        <table class="tabla-carrito" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th>Método</th>
                                    <th>Monto</th>
                                    <th>Moneda</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="pagos-body-tpv">
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #999;">No hay pagos agregados</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="detalle-pago" style="background-color: #f0f0f0; padding: 8px; border-radius: 4px; margin-bottom: 8px;">
                        <p><strong>Total Pagado:</strong></p>
                        <p id="total-pagado-tpv" style="color: #039438; font-weight: bold;">$0.00</p>
                    </div>

                    <div class="detalle-pago" id="falta-pagar-container" style="background-color: #fff3cd; padding: 8px; border-radius: 4px; margin-bottom: 8px; display: none;">
                        <p><strong>Falta por Pagar:</strong></p>
                        <p id="falta-pagar-usd-tpv" style="color: #856404; font-weight: bold; margin: 2px 0;">$0.00 USD</p>
                        <p id="falta-pagar-bs-tpv" style="color: #856404; font-weight: bold; margin: 2px 0;">Bs0.00</p>
                    </div>

                    <div class="detalle-pago" id="cambio-container" style="display: none;">
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
    pagos: [], // Array de pagos múltiples
    
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
    
    init: function() {
        // CACHE BUSTER: 2025-12-05-10:35:00-v3.0-FIXED
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
        document.getElementById('btn-agregar-pago').addEventListener('click', () => this.agregarPago());
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
    
    // 5B. AGREGAR PAGO
    agregarPago: function() {
        const metodoPagoSelect = document.getElementById('pago-metodo-tpv');
        const montoInput = document.getElementById('monto-pago-tpv');
        
        const paymentMethodId = parseInt(metodoPagoSelect.value);
        const amount = parseFloat(montoInput.value);
        
        if (!paymentMethodId || isNaN(amount) || amount <= 0) {
            Toastify({
                text: "⚠️ Selecciona un método de pago y un monto válido",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#f39c12"
            }).showToast();
            return;
        }
        
        const selectedOption = metodoPagoSelect.options[metodoPagoSelect.selectedIndex];
        const paymentMethodName = selectedOption.text;
        const currency = selectedOption.getAttribute('data-currency');
        
        this.pagos.push({
            payment_method_id: paymentMethodId,
            payment_method_name: paymentMethodName,
            amount: amount,
            currency: currency
        });
        
        // Limpiar inputs
        montoInput.value = '';
        
        this.actualizarVistaPagos();
        
        Toastify({
            text: "✅ Pago agregado",
            duration: 2000,
            gravity: "top",
            position: "right",
            backgroundColor: "#27ae60"
        }).showToast();
    },
    
    // 5C. ELIMINAR PAGO
    eliminarPago: function(index) {
        this.pagos.splice(index, 1);
        this.actualizarVistaPagos();
    },
    
    // 5D. ACTUALIZAR VISTA DE PAGOS
    actualizarVistaPagos: function() {
        const tbody = document.getElementById('pagos-body-tpv');
        
        if (this.pagos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #999;">No hay pagos agregados</td></tr>';
            document.getElementById('total-pagado-tpv').textContent = '$0.00';
            document.getElementById('falta-pagar-container').style.display = 'none';
            document.getElementById('cambio-container').style.display = 'none';
            return;
        }
        
        let html = '';
        this.pagos.forEach((pago, index) => {
            const symbol = pago.currency === 'Bs' ? 'Bs' : '$';
            html += `
                <tr>
                    <td>${pago.payment_method_name}</td>
                    <td>${symbol}${pago.amount.toFixed(2)}</td>
                    <td>${pago.currency}</td>
                    <td>
                        <button class="btn-eliminar-pago" data-index="${index}" style="background-color: #e74c3c; color: white; border: none; padding: 6px 10px; border-radius: 3px; cursor: pointer; font-size: 16px; font-weight: bold;">×</button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        
        // Agregar event listeners a los botones de eliminar
        document.querySelectorAll('.btn-eliminar-pago').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.getAttribute('data-index'));
                this.eliminarPago(index);
            });
        });
        
        // Calcular totales - convertir todo a USD primero
        const totalPagadoUSD = this.pagos.reduce((sum, pago) => {
            if (pago.currency === 'USD') {
                return sum + pago.amount;
            } else {
                // Convertir Bs a USD
                return sum + (pago.amount / this.exchangeRate);
            }
        }, 0);
        
        const totalElement = document.getElementById('total-tpv');
        const totalFullText = totalElement.textContent || totalElement.innerText;
        const totalTexto = totalFullText.replace(/[$Bs]/g, '').trim();
        const totalVenta = parseFloat(totalTexto) || 0;
        
        // Detectar si el total está en Bs o USD
        const totalEnBs = totalFullText.indexOf('Bs') !== -1;
        
        // Convertir total pagado a la moneda del total para comparación
        const totalPagadoEnMonedaVenta = totalEnBs ? (totalPagadoUSD * this.exchangeRate) : totalPagadoUSD;
        
        document.getElementById('total-pagado-tpv').textContent = '$' + totalPagadoUSD.toFixed(2);
        
        if (totalPagadoEnMonedaVenta < totalVenta) {
            const falta = totalVenta - totalPagadoEnMonedaVenta;
            // Convertir falta a ambas monedas
            const faltaUSD = totalEnBs ? (falta / this.exchangeRate) : falta;
            const faltaBs = totalEnBs ? falta : (falta * this.exchangeRate);
            
            document.getElementById('falta-pagar-usd-tpv').textContent = '$' + faltaUSD.toFixed(2) + ' USD';
            document.getElementById('falta-pagar-bs-tpv').textContent = 'Bs' + faltaBs.toFixed(2);
            document.getElementById('falta-pagar-container').style.display = 'flex';
            document.getElementById('cambio-container').style.display = 'none';
        } else if (totalPagadoEnMonedaVenta > totalVenta) {
            const cambio = totalPagadoEnMonedaVenta - totalVenta;
            const cambioUSD = totalEnBs ? (cambio / this.exchangeRate) : cambio;
            document.getElementById('cambio-tpv').textContent = '$' + cambioUSD.toFixed(2);
            document.getElementById('cambio-container').style.display = 'flex';
            document.getElementById('falta-pagar-container').style.display = 'none';
        } else {
            document.getElementById('falta-pagar-container').style.display = 'none';
            document.getElementById('cambio-container').style.display = 'none';
        }
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
        
        const totalElement = document.getElementById('total-tpv');
        const totalFullText = totalElement.textContent || totalElement.innerText;
        const totalTexto = totalFullText.replace(/[$Bs]/g, '').trim();
        const total = parseFloat(totalTexto);
        
        // Validar que hay pagos agregados
        if (this.pagos.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin métodos de pago',
                text: 'Debes agregar al menos un método de pago.',
                confirmButtonColor: '#3498db'
            });
            return;
        }
        
        // Detectar moneda del total (búsqueda insensible a mayúsculas/minúsculas)
        const totalEnBs = /bs/i.test(totalFullText);
        
        // Calcular total pagado en USD
        const totalPagadoUSD = this.pagos.reduce((sum, pago) => {
            if (pago.currency === 'USD') {
                return sum + pago.amount;
            } else {
                return sum + (pago.amount / this.exchangeRate);
            }
        }, 0);
        
        // ===== VALIDACIÓN DE PAGO SUFICIENTE (v3.0) =====
        // Convertir todo a USD para comparar manzanas con manzanas
        const totalEnUSD = totalEnBs ? (total / this.exchangeRate) : total;
        const faltaEnUSD = totalEnUSD - totalPagadoUSD;
        
        console.log('🔍 VALIDACIÓN v3.0:', { 
            totalEnUSD: totalEnUSD.toFixed(4),
            totalPagadoUSD: totalPagadoUSD.toFixed(4),
            faltaEnUSD: faltaEnUSD.toFixed(6),
            bloqueado: faltaEnUSD > 0.01
        });
        
        // Solo bloquear si falta MÁS de 1 centavo
        if (faltaEnUSD > 0.01) {
            Swal.fire({
                icon: 'warning',
                title: 'Monto insuficiente',
                html: `<p>Falta por pagar: <b>$${faltaEnUSD.toFixed(2)} USD</b> / <b>Bs${(faltaEnUSD * this.exchangeRate).toFixed(2)}</b></p>
                       <hr>
                       <small style="color:#999">
                       Total: $${totalEnUSD.toFixed(2)} USD<br>
                       Pagado: $${totalPagadoUSD.toFixed(2)} USD<br>
                       Diferencia exacta: $${faltaEnUSD.toFixed(4)}
                       </small>`,
                confirmButtonColor: '#3498db'
            });
            return;
        }
        
        console.log('💳 TPV Datos pago:', { total, totalPagadoUSD, pagos: this.pagos });
        
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
            payments: this.pagos.map(pago => ({
                payment_method_id: pago.payment_method_id,
                amount: pago.amount,
                currency: pago.currency
            })),
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
                
                // Handle CSRF token mismatch
                if (error.message && error.message.includes('CSRF')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesión expirada',
                        html: 'Tu sesión ha expirado. Por favor, <b>recarga la página</b> (F5) e intenta de nuevo.',
                        confirmButtonText: 'Recargar página',
                        confirmButtonColor: '#3498db'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                    return;
                }
                
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
        this.pagos = []; // Reset payments array
        
        // Reset selects
        document.getElementById('producto-select-tpv').selectedIndex = 0;
        document.getElementById('servicio-select-tpv').selectedIndex = 0;
        document.getElementById('cliente-rfc-input').value = '';
        
        // Reset payment fields
        document.getElementById('monto-pago-tpv').value = '';
        document.getElementById('pago-metodo-tpv').selectedIndex = 0;
        
        // Hide customer info
        document.getElementById('cliente-info-tpv').style.display = 'none';
        document.getElementById('cliente-texto-tpv').innerHTML = '';
        
        // Re-enable both selects
        document.getElementById('producto-select-tpv').disabled = false;
        document.getElementById('servicio-select-tpv').disabled = false;
        
        this.actualizarVistaCarrito();
        this.actualizarVistaPagos(); // Update payment view
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