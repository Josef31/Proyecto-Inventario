// ==============================================================================
// 0. CONFIGURACIÓN Y ESTADOS GLOBALES
// ==============================================================================

// --- Variables Globales de Módulos ---
let inventario = [];       
let servicios = [];        
let carrito = [];          
let facturas = [];         

// --- Variables Globales de Caja ---
let estadoCaja = null;     
let historialCortes = [];  
let ventasDelDia = [];     

// --- Constantes de Reglas de Negocio ---
const MARGEN_MINIMO = 0.30;          
const ALERTA_STOCK_MINIMO = 5;       
const IVA_RATE = 0.16;               

// ==============================================================================
// 1. GESTIÓN DE DATOS (Persistencia con APIs Laravel)
// ==============================================================================

/** Guarda el estado actual de todos los módulos en localStorage. */
function guardarDatos() {
    localStorage.setItem('carritoData', JSON.stringify(carrito));
    localStorage.setItem('estadoCaja', JSON.stringify(estadoCaja));
    localStorage.setItem('historialCortes', JSON.stringify(historialCortes));
    localStorage.setItem('ventasDelDia', JSON.stringify(ventasDelDia));
}

/** Carga los datos desde localStorage */
function cargarDatos() {
    const carritoData = localStorage.getItem('carritoData');
    const cajaData = localStorage.getItem('estadoCaja');
    const cortesData = localStorage.getItem('historialCortes');
    const ventasDiaData = localStorage.getItem('ventasDelDia');
    
    if (carritoData) carrito = JSON.parse(carritoData);
    if (cajaData && cajaData !== 'null') estadoCaja = JSON.parse(cajaData);
    if (cortesData) historialCortes = JSON.parse(cortesData);
    if (ventasDiaData) ventasDelDia = JSON.parse(ventasDiaData);
}

/** Muestra la alerta verde de éxito. */
function mostrarAlertaExito() {
    const alerta = document.getElementById('alerta-exito');
    if (alerta) {
        alerta.style.display = 'flex';
        setTimeout(() => { alerta.style.display = 'none'; }, 3000);
    }
}

// ==============================================================================
// 2. GESTIÓN DE MÓDULO INVENTARIO
// ==============================================================================

/** Listener para calcular el precio de venta mínimo (Precio Compra + 30%). */
document.addEventListener('DOMContentLoaded', function() {
    const precioCompraInput = document.getElementById('precio-compra');
    if (precioCompraInput) {
        precioCompraInput.addEventListener('input', function() {
            const precioCompra = parseFloat(this.value);
            const precioVentaInput = document.getElementById('precio-venta');
            if (isNaN(precioCompra) || precioCompra <= 0) {
                precioVentaInput.value = '';
                return;
            }
            const precioVentaMinimo = precioCompra * (1 + MARGEN_MINIMO);
            precioVentaInput.value = precioVentaMinimo.toFixed(2);
        });
    }
});

/** Agrega un producto de inventario. */
function agregarProducto() {
    const nombre = document.getElementById('nombre').value.trim();
    const precioCompra = parseFloat(document.getElementById('precio-compra').value);
    const precioVenta = parseFloat(document.getElementById('precio-venta').value);
    
    if (nombre === '' || isNaN(precioCompra) || isNaN(precioVenta)) {
        alert('Por favor, rellena todos los campos de forma correcta.');
        return;
    }

    if (precioVenta < (precioCompra * (1 + MARGEN_MINIMO) - 0.01)) {
        alert('Error: El precio de venta no cumple con el margen mínimo del 30% requerido.');
        return;
    }
    
    const formData = new FormData();
    formData.append('name', nombre);
    formData.append('classification', document.getElementById('clasificacion').value);
    formData.append('price_buy', precioCompra);
    formData.append('price_sell', precioVenta);
    formData.append('stock_initial', parseInt(document.getElementById('stock-actual').value) || 0);
    formData.append('stock_minimum', parseInt(document.getElementById('stock-minimo').value) || 0);
    formData.append('expiration_date', document.getElementById('fecha-vencimiento').value || null);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/api/inventory', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        mostrarAlertaExito();
        actualizarTablaInventario();
        actualizarTotalInvertido();
        document.getElementById('form-inventario').reset();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el producto');
    });
}

/** Actualiza la tabla de productos con alertas visuales. */
function actualizarTablaInventario() {
    const tbody = document.getElementById('tabla-cuerpo');
    if (!tbody) return; 
    
    fetch('/api/inventory')
    .then(response => response.json())
    .then(inventario => {
        tbody.innerHTML = '';
        inventario.forEach(producto => {
            let claseFila = '';
            let claseStock = '';
            let claseVencimiento = '';

            if (producto.stock_actual <= 0) {
                claseStock = 'stock-agotado';
                claseFila = 'alerta-vencimiento';
            } else if (producto.stock_actual <= producto.stock_minimum || producto.stock_actual <= ALERTA_STOCK_MINIMO) {
                claseStock = 'stock-bajo';
                claseFila = 'alerta-stock';
            }

            const fechaVenc = producto.expiration_date ? new Date(producto.expiration_date) : null;
            const fechaLimite = new Date();
            fechaLimite.setMonth(fechaLimite.getMonth() + 1);
            const fechaTexto = producto.expiration_date ? new Date(producto.expiration_date).toISOString().split('T')[0] : 'N/A';

            if (fechaVenc && !isNaN(fechaVenc.getTime()) && fechaVenc < fechaLimite) {
                claseVencimiento = 'vencimiento-cerca';
                claseFila = 'alerta-vencimiento';
            }
            
            const fila = document.createElement('tr');
            fila.className = claseFila;

            fila.innerHTML = `
                <td>${producto.id}</td>
                <td>${producto.name}</td>
                <td>${producto.classification}</td>
                <td><span class="${claseStock}">${producto.stock_actual}</span></td>
                <td>${producto.stock_minimum}</td>
                <td><span class="${claseVencimiento}">${fechaTexto}</span></td>
                <td>${parseFloat(producto.price_sell).toFixed(2)}</td>
                <td class="acciones">
                    <button class="btn-detalles">Detalles</button>
                    <button class="btn-eliminar" onclick="eliminarProducto(${producto.id})">Eliminar</button>
                </td>
            `;
            tbody.appendChild(fila);
        });
    })
    .catch(error => console.error('Error:', error));
}

/** Calcula y actualiza el valor total invertido en stock. */
function actualizarTotalInvertido() {
    const totalElement = document.getElementById('total-invertido');
    if (totalElement) {
        fetch('/api/inventory')
        .then(response => response.json())
        .then(inventario => {
            const total = inventario.reduce((sum, producto) => sum + (parseFloat(producto.price_buy) * producto.stock_actual), 0);
            totalElement.textContent = total.toFixed(2);
        })
        .catch(error => console.error('Error:', error));
    }
}

/** Elimina un producto del inventario. */
function eliminarProducto(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este artículo?')) {
        return;
    }

    fetch(`/api/inventory/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        actualizarTablaInventario();
        actualizarTotalInvertido();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el producto');
    });
}

// ==============================================================================
// 3. GESTIÓN DE MÓDULO SERVICIOS
// ==============================================================================

/** Agrega un nuevo servicio al listado. */
function agregarServicio() {
    const nombre = document.getElementById('nombre-servicio').value.trim();
    const costoBase = parseFloat(document.getElementById('costo-base').value);
    const tarifaCliente = parseFloat(document.getElementById('tarifa-cliente').value);
    const duracion = parseInt(document.getElementById('duracion-estimada').value);

    if (nombre === '' || isNaN(costoBase) || isNaN(tarifaCliente) || isNaN(duracion)) {
        alert('Por favor, rellena todos los campos del servicio.');
        return;
    }
    
    const formData = new FormData();
    formData.append('name', nombre);
    formData.append('base_cost', costoBase);
    formData.append('client_rate', tarifaCliente);
    formData.append('duration', duracion);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/api/services', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        mostrarAlertaExito();
        actualizarTablaServicios();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el servicio');
    });
}

/** Actualiza la tabla de servicios y los totales. */
function actualizarTablaServicios() {
    const tbody = document.getElementById('tabla-cuerpo-servicios');
    const totalServicios = document.getElementById('total-servicios');
    if (!tbody) return; 
    
    fetch('/api/services')
    .then(response => response.json())
    .then(servicios => {
        tbody.innerHTML = '';
        
        servicios.forEach(servicio => {
            const ganancia = servicio.client_rate - servicio.base_cost;
            
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${servicio.id}</td>
                <td>${servicio.name}</td>
                <td>${parseFloat(servicio.base_cost).toFixed(2)}</td>
                <td>${parseFloat(servicio.client_rate).toFixed(2)}</td>
                <td>${servicio.duration} min</td>
                <td><span style="color: ${ganancia > 0 ? '#2ecc71' : '#e74c3c'}">${ganancia.toFixed(2)}</span></td>
                <td class="acciones">
                    <button class="btn-detalles">Editar</button>
                    <button class="btn-eliminar" onclick="eliminarServicio(${servicio.id})">Eliminar</button>
                </td>
            `;
            tbody.appendChild(fila);
        });
        
        if (totalServicios) totalServicios.textContent = servicios.length;
    })
    .catch(error => console.error('Error:', error));
}

/** Elimina un servicio del listado. */
function eliminarServicio(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este servicio?')) {
        return;
    }

    fetch(`/api/services/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        actualizarTablaServicios();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el servicio');
    });
}

// ==============================================================================
// 4. GESTIÓN DE MÓDULO VENTAS (TPV)
// ==============================================================================

/** Busca productos (Inventario) y servicios. */
function buscarProducto() {
    const query = document.getElementById('input-busqueda').value.toLowerCase();
    const resultadosDiv = document.getElementById('resultado-busqueda');
    resultadosDiv.innerHTML = '';

    if (query.length < 2) return;

    // Buscar en productos
    fetch('/api/inventory')
    .then(response => response.json())
    .then(inventario => {
        const resultadosInv = inventario.filter(p => 
            (p.name.toLowerCase().includes(query) || p.id.toString() === query) && p.stock_actual > 0
        ).map(p => ({ ...p, tipo: 'producto', precioVenta: parseFloat(p.price_sell) }));

        // Buscar en servicios
        return fetch('/api/services')
        .then(response => response.json())
        .then(servicios => {
            const resultadosServ = servicios.filter(s => 
                s.name.toLowerCase().includes(query) || s.id.toString() === query
            ).map(s => ({ ...s, tipo: 'servicio', precioVenta: parseFloat(s.client_rate) }));

            const resultados = [...resultadosInv, ...resultadosServ];
            
            resultados.forEach(p => {
                const item = document.createElement('div');
                item.className = 'resultado-item';
                const tipoEtiqueta = p.tipo === 'producto' ? 'P' : 'S';
                
                item.innerHTML = `
                    <span>[${tipoEtiqueta}] ${p.name} - $${p.precioVenta.toFixed(2)}</span>
                    <button onclick="agregarAlCarrito(${p.id}, '${p.tipo}')">Añadir</button>
                `;
                resultadosDiv.appendChild(item);
            });
            
            if (resultados.length === 0) {
                resultadosDiv.innerHTML = '<p style="padding: 10px; color: #e74c3c;">No se encontraron productos/servicios disponibles.</p>';
            }
        });
    })
    .catch(error => console.error('Error:', error));
}

/** Agrega un producto o servicio al carrito. */
function agregarAlCarrito(itemId, tipo) {
    let item;
    
    if (tipo === 'producto') {
        fetch(`/api/inventory/${itemId}`)
        .then(response => response.json())
        .then(producto => {
            item = producto;
            item.precioVenta = parseFloat(producto.price_sell);
            procesarAgregarCarrito(item, tipo, itemId);
        })
        .catch(error => console.error('Error:', error));
    } else {
        fetch(`/api/services/${itemId}`)
        .then(response => response.json())
        .then(servicio => {
            item = servicio;
            item.precioVenta = parseFloat(servicio.client_rate);
            procesarAgregarCarrito(item, tipo, itemId);
        })
        .catch(error => console.error('Error:', error));
    }
}

function procesarAgregarCarrito(item, tipo, itemId) {
    const itemExistente = carrito.find(c => c.id === itemId && c.tipo === tipo);

    if (itemExistente) {
        if (tipo === 'producto' && itemExistente.cantidad >= item.stock_actual) {
            alert('Stock insuficiente.');
            return;
        }
        itemExistente.cantidad++;
    } else {
        carrito.push({
            id: item.id,
            nombre: item.name,
            precio: item.precioVenta,
            cantidad: 1,
            tipo: tipo
        });
    }

    const busquedaInput = document.getElementById('input-busqueda');
    if (busquedaInput) busquedaInput.value = ''; 
    const resultadosDiv = document.getElementById('resultado-busqueda');
    if (resultadosDiv) resultadosDiv.innerHTML = '';

    actualizarCarritoDOM();
    guardarDatos();
}

/** Recalcula totales y actualiza la tabla del carrito. */
function actualizarCarritoDOM() {
    const cuerpoCarrito = document.getElementById('cuerpo-carrito');
    const totalSubtotal = document.getElementById('total-subtotal');
    const totalImpuestos = document.getElementById('total-impuestos');
    const totalPagar = document.getElementById('total-pagar');
    const montoRecibidoInput = document.getElementById('monto-recibido');
    const montoCambio = document.getElementById('monto-cambio');
    
    if (!cuerpoCarrito) return; 

    cuerpoCarrito.innerHTML = '';
    let subtotal = 0;

    carrito.forEach(item => {
        const itemSubtotal = item.precio * item.cantidad;
        subtotal += itemSubtotal;

        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${item.nombre}</td>
            <td>
                ${item.cantidad}
                <button class="btn-quitar" style="background-color: #2ecc71;" onclick="incrementarCantidad(${item.id}, '${item.tipo}')">+</button>
                <button class="btn-quitar" onclick="quitarDelCarrito(${item.id}, '${item.tipo}')">-</button>
            </td>
            <td>${item.precio.toFixed(2)}</td>
            <td>${itemSubtotal.toFixed(2)}</td>
            <td><button class="btn-quitar" onclick="eliminarItemCompleto(${item.id}, '${item.tipo}')">X</button></td>
        `;
        cuerpoCarrito.appendChild(fila);
    });

    const impuestos = subtotal * IVA_RATE;
    const total = subtotal + impuestos;
    
    if (totalSubtotal) totalSubtotal.textContent = subtotal.toFixed(2);
    if (totalImpuestos) totalImpuestos.textContent = impuestos.toFixed(2);
    if (totalPagar) totalPagar.textContent = total.toFixed(2);

    const recibido = parseFloat(montoRecibidoInput ? montoRecibidoInput.value : 0) || 0;
    const cambio = recibido > total ? recibido - total : 0;
    if (montoCambio) montoCambio.textContent = cambio.toFixed(2);
    
    guardarDatos();
}

function incrementarCantidad(itemId, tipo) {
    const itemExistente = carrito.find(c => c.id === itemId && c.tipo === tipo);
    if (itemExistente) {
        if (tipo === 'producto') {
            fetch(`/api/inventory/${itemId}`)
            .then(response => response.json())
            .then(productoInventario => {
                if (itemExistente.cantidad >= productoInventario.stock_actual) {
                    alert('Stock insuficiente.');
                    return;
                }
                itemExistente.cantidad++;
                actualizarCarritoDOM();
            })
            .catch(error => console.error('Error:', error));
        } else {
            itemExistente.cantidad++;
            actualizarCarritoDOM();
        }
    }
}

function quitarDelCarrito(itemId, tipo) {
    const itemIndex = carrito.findIndex(c => c.id === itemId && c.tipo === tipo);
    if (itemIndex > -1) {
        carrito[itemIndex].cantidad--;
        if (carrito[itemIndex].cantidad <= 0) {
            carrito.splice(itemIndex, 1);
        }
    }
    actualizarCarritoDOM();
}

function eliminarItemCompleto(itemId, tipo) {
    carrito = carrito.filter(c => !(c.id === itemId && c.tipo === tipo));
    actualizarCarritoDOM();
}

/** Procesa el pago, descuenta el stock y genera la factura. */
function finalizarVenta() {
    const totalAPagarElement = document.getElementById('total-pagar');
    const montoRecibidoInput = document.getElementById('monto-recibido');
    const metodoPagoElement = document.getElementById('metodo-pago'); 
    
    if (!totalAPagarElement || !montoRecibidoInput) {
         alert('Error: No se encontraron elementos de totales o pago.'); return;
    }
    
    const totalAPagar = parseFloat(totalAPagarElement.textContent);
    const montoRecibido = parseFloat(montoRecibidoInput.value);
    const metodoPago = metodoPagoElement ? metodoPagoElement.value : 'efectivo';

    if (carrito.length === 0) { alert('El carrito está vacío.'); return; }
    if (montoRecibido < totalAPagar) { alert('El monto recibido es insuficiente para cubrir el total.'); return; }

    const ventaData = {
        items: carrito,
        total: totalAPagar,
        monto_recibido: montoRecibido,
        metodo_pago: metodoPago
    };

    fetch('/api/sales', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(ventaData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        alert(`Venta finalizada con éxito. Factura #${data.invoice_id} generada. Su cambio: $${(montoRecibido - totalAPagar).toFixed(2)}`);
        
        carrito = [];
        if (montoRecibidoInput) montoRecibidoInput.value = '';
        actualizarCarritoDOM();
        localStorage.removeItem('carritoData');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la venta');
    });
}

/** Cancela la venta actual y vacía el carrito. */
function cancelarVenta() {
    if (confirm('¿Deseas cancelar la venta actual y vaciar el carrito?')) {
        carrito = [];
        const montoRecibidoInput = document.getElementById('monto-recibido');
        if (montoRecibidoInput) montoRecibidoInput.value = '';
        actualizarCarritoDOM();
        localStorage.removeItem('carritoData');
    }
}

// ==============================================================================
// 5. GESTIÓN DE MÓDULO FACTURAS
// ==============================================================================

/** Actualiza la tabla con el historial de facturas generadas. */
function actualizarTablaFacturas() {
    const tbody = document.getElementById('tabla-cuerpo-facturas');
    const totalFacturas = document.getElementById('total-facturas');
    if (!tbody) return;

    fetch('/api/invoices')
    .then(response => response.json())
    .then(facturas => {
        tbody.innerHTML = '';
        
        [...facturas].reverse().forEach(factura => {
            const fecha = new Date(factura.created_at).toLocaleDateString() + ' ' + new Date(factura.created_at).toLocaleTimeString();
            
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>#${factura.id}</td>
                <td>${fecha}</td>
                <td>${factura.items_count}</td>
                <td>${factura.metodo_pago.toUpperCase()}</td>
                <td>$${parseFloat(factura.total).toFixed(2)}</td>
                <td class="acciones">
                    <button class="btn-detalles" onclick="verDetalleFactura(${factura.id})">Ver</button>
                </td>
            `;
            tbody.appendChild(fila);
        });
        
        if (totalFacturas) totalFacturas.textContent = facturas.length;
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Muestra el detalle de una factura específica.
 * @param {number} facturaId ID de la factura a mostrar.
 */
function verDetalleFactura(facturaId) {
    fetch(`/api/invoices/${facturaId}`)
    .then(response => response.json())
    .then(factura => {
        let detalleItems = factura.items.map(item => `
            <li>${item.cantidad} x ${item.nombre} @ $${parseFloat(item.precio).toFixed(2)} = $${(item.cantidad * item.precio).toFixed(2)}</li>
        `).join('');

        const modalContent = `
            <h3>Factura #${factura.id}</h3>
            <p><strong>Fecha:</strong> ${new Date(factura.created_at).toLocaleString()}</p>
            <p><strong>Cajero:</strong> ${factura.cajero}</p>
            <hr>
            <h4>Detalle de Ítems:</h4>
            <ul style="list-style-type: none; padding: 0;">${detalleItems}</ul>
            <hr>
            <p>Subtotal: <strong>$${parseFloat(factura.subtotal).toFixed(2)}</strong></p>
            <p>Impuestos (${(IVA_RATE * 100).toFixed(0)}%): <strong>$${parseFloat(factura.impuestos).toFixed(2)}</strong></p>
            <p style="font-size: 1.2em; font-weight: bold;">TOTAL: $${parseFloat(factura.total).toFixed(2)}</p>
            <p>Método de Pago: ${factura.metodo_pago.toUpperCase()}</p>
            <p>Monto Recibido: $${parseFloat(factura.monto_recibido).toFixed(2)}</p>
            <p>Cambio: $${parseFloat(factura.cambio).toFixed(2)}</p>
        `;

        // Crear modal simple
        const modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = '1000';
        
        modal.innerHTML = `
            <div style="background: white; padding: 20px; border-radius: 8px; max-width: 500px; max-height: 80vh; overflow-y: auto;">
                ${modalContent}
                <button onclick="this.parentElement.parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
            </div>
        `;
        
        document.body.appendChild(modal);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los detalles de la factura');
    });
}

// ==============================================================================
// 6. GESTIÓN DE MÓDULO ESTADO DE CAJA
// ==============================================================================

/** Abre la caja registrando un monto inicial. */
function abrirCaja() {
    const montoInicialInput = document.getElementById('monto-inicial');
    if (!montoInicialInput) return;

    const montoInicial = parseFloat(montoInicialInput.value);

    if (isNaN(montoInicial) || montoInicial <= 0) {
        alert('Por favor, ingresa un monto inicial válido.');
        return;
    }

    const formData = new FormData();
    formData.append('monto_inicial', montoInicial);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/api/cash/open', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        estadoCaja = data.caja;
        guardarDatos();
        actualizarVistaCaja();
        alert(`Caja abierta con éxito. Fondo inicial: $${montoInicial.toFixed(2)}`);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al abrir la caja');
    });
}

/** Cierra la caja, calcula la diferencia y registra el corte. */
function cerrarCaja() {
    if (!estadoCaja) {
        alert('La caja ya está cerrada o nunca se abrió.');
        return;
    }

    const montoFinalInput = document.getElementById('monto-final-registrado');
    if (!montoFinalInput) return;

    const montoFinalRegistrado = parseFloat(montoFinalInput.value);
    
    if (isNaN(montoFinalRegistrado) || montoFinalRegistrado < 0) {
        alert('Por favor, ingresa el monto final físico registrado.');
        return;
    }
    
    const formData = new FormData();
    formData.append('monto_final', montoFinalRegistrado);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/api/cash/close', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        estadoCaja = null;
        ventasDelDia = [];
        guardarDatos();
        actualizarVistaCaja();
        actualizarTablaCortes();
        alert('Corte de caja finalizado y registrado.');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cerrar la caja');
    });
}

/** Actualiza la vista de la barra lateral (Panel de Apertura/Cierre). */
function actualizarVistaCaja() {
    const panelApertura = document.getElementById('panel-apertura');
    const panelCierre = document.getElementById('panel-cierre');
    const titulo = document.getElementById('titulo-caja');
    
    if (!panelApertura || !panelCierre || !titulo) return;

    if (estadoCaja) {
        titulo.textContent = 'CIERRE DE CAJA DIARIO';
        panelApertura.style.display = 'none';
        panelCierre.style.display = 'block';

        document.getElementById('fecha-apertura-display').textContent = new Date(estadoCaja.fecha_apertura).toLocaleTimeString();
        document.getElementById('caja-fondo').textContent = parseFloat(estadoCaja.monto_inicial).toFixed(2);
        document.getElementById('ventas-efectivo').textContent = parseFloat(estadoCaja.ventas_efectivo || 0).toFixed(2);
        document.getElementById('total-esperado').textContent = parseFloat(estadoCaja.total_esperado || estadoCaja.monto_inicial).toFixed(2);
        
        const montoFinalInput = document.getElementById('monto-final-registrado');
        if (montoFinalInput) {
            montoFinalInput.value = parseFloat(estadoCaja.total_esperado || estadoCaja.monto_inicial).toFixed(2);
        }

    } else {
        titulo.textContent = 'APERTURA DE CAJA';
        panelApertura.style.display = 'block';
        panelCierre.style.display = 'none';
    }
}

/** Actualiza la tabla con el historial de cortes de caja. */
function actualizarTablaCortes() {
    const tbody = document.getElementById('tabla-cuerpo-cortes');
    const cortesRegistrados = document.getElementById('cortes-registrados');
    if (!tbody) return;

    fetch('/api/cash/history')
    .then(response => response.json())
    .then(historialCortes => {
        tbody.innerHTML = '';
        
        [...historialCortes].reverse().forEach(corte => {
            const fechaApertura = new Date(corte.fecha_apertura).toLocaleDateString() + ' ' + new Date(corte.fecha_apertura).toLocaleTimeString();
            
            let diferenciaClase = '';
            if (corte.diferencia === 0) {
                diferenciaClase = 'stock-bajo';
            } else if (corte.diferencia > 0) {
                diferenciaClase = 'alerta-stock';
            } else {
                diferenciaClase = 'alerta-vencimiento';
            }

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${fechaApertura}</td>
                <td>${parseFloat(corte.fondo_inicial).toFixed(2)}</td>
                <td>${parseFloat(corte.ventas_efectivo).toFixed(2)}</td>
                <td>${parseFloat(corte.monto_final_fisico).toFixed(2)}</td>
                <td><span class="${diferenciaClase}">${parseFloat(corte.diferencia).toFixed(2)}</span></td>
                <td>${corte.cajero}</td>
            `;
            tbody.appendChild(fila);
        });
        
        if (cortesRegistrados) cortesRegistrados.textContent = historialCortes.length;
    })
    .catch(error => console.error('Error:', error));
}

// ==============================================================================
// 7. INICIALIZACIÓN DE LA APLICACIÓN
// ==============================================================================

/** Se ejecuta cuando la página ha terminado de cargar. */
document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();

    const pathname = window.location.pathname;

    if (pathname.includes('inventory') || pathname.endsWith('/')) {
        actualizarTablaInventario();
        actualizarTotalInvertido();
    } 
    else if (pathname.includes('services')) {
        actualizarTablaServicios();
    }
    else if (pathname.includes('sales')) {
        actualizarCarritoDOM();
        const busquedaInput = document.getElementById('input-busqueda');
        if (busquedaInput) {
            busquedaInput.addEventListener('input', buscarProducto);
        }
    }
    else if (pathname.includes('cash')) {
        actualizarVistaCaja();
        actualizarTablaCortes();
    }
    else if (pathname.includes('invoices')) {
        actualizarTablaFacturas();
    }
});