


// ==============================================================================
// 0. CONFIGURACIÓN Y ESTADOS GLOBALES
// ==============================================================================

// --- Variables Globales de Módulos ---
let inventario = [];       
let servicios = [];        
let carrito = [];          
let facturas = [];         // ¡NUEVO! Array para almacenar las facturas generadas

// --- Variables Globales de Caja ---
let estadoCaja = null;     
let historialCortes = [];  
let ventasDelDia = [];     

// --- Contadores para IDs únicos ---
let nextId = 1;            // Contador para ID de productos
let nextServicioId = 1;    // Contador para ID de servicios
let nextFacturaId = 1001;  // Contador para ID de facturas (inicia en 1001)

// --- Constantes de Reglas de Negocio ---
const MARGEN_MINIMO = 0.30;          
const ALERTA_STOCK_MINIMO = 5;       
const IVA_RATE = 0.16;               

// --- Constantes de Autenticación (Login) ---
const USUARIO_VALIDO = 'luishck';
const CONTRASENA_VALIDA = 'admin123'; 


// ==============================================================================
// 1. LÓGICA DE AUTENTICACIÓN (LOGIN)
// ==============================================================================

/** Valida las credenciales e inicia sesión. */
function iniciarSesion() {
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const errorMsg = document.getElementById('login-error');

    if (!usernameInput || !passwordInput) {
        console.error("Error: Elementos 'username' o 'password' no encontrados en el DOM.");
        return;
    }

    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();

    if (username === USUARIO_VALIDO && password === CONTRASENA_VALIDA) {
        localStorage.setItem('isAuthenticated', 'true'); 
        window.location.href = 'index.html'; 
    } else {
        if (errorMsg) {
            errorMsg.textContent = "Usuario o contraseña incorrectos.";
            errorMsg.style.display = 'block'; 
            setTimeout(() => { errorMsg.style.display = 'none'; }, 3000);
        }
    }
}

/** Verifica la sesión al cargar CUALQUIER página. */
function checkAuthentication() {
    const pathname = window.location.pathname;
    const isLoginPage = pathname.endsWith('login.html') || pathname.endsWith('/'); 
    const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
    
    if (!isLoginPage && !isAuthenticated) {
        window.location.href = 'login.html';
        return false;
    }
    if (isLoginPage && isAuthenticated && !pathname.endsWith('index.html')) {
        window.location.href = 'index.html';
        return false;
    }
    return isAuthenticated; 
}


// ==============================================================================
// 2. GESTIÓN DE DATOS (Persistencia con localStorage)
// ==============================================================================

/** Guarda el estado actual de todos los módulos en localStorage. */
function guardarDatos() {
    localStorage.setItem('inventarioData', JSON.stringify(inventario));
    localStorage.setItem('serviciosData', JSON.stringify(servicios));
    localStorage.setItem('estadoCaja', JSON.stringify(estadoCaja));
    localStorage.setItem('historialCortes', JSON.stringify(historialCortes));
    localStorage.setItem('ventasDelDia', JSON.stringify(ventasDelDia)); 
    localStorage.setItem('facturasData', JSON.stringify(facturas)); // ¡NUEVO! Guardar facturas
}

/** Carga los datos o inicializa con ejemplos de Ferretería. */
function cargarDatos() {
    const invData = localStorage.getItem('inventarioData');
    const servData = localStorage.getItem('serviciosData');
    const cajaData = localStorage.getItem('estadoCaja');
    const cortesData = localStorage.getItem('historialCortes');
    const ventasDiaData = localStorage.getItem('ventasDelDia');
    const facturasData = localStorage.getItem('facturasData'); // ¡NUEVO! Cargar facturas
    
    // Cargar Inventario y Servicios
    if (invData) {
        inventario = JSON.parse(invData);
        const maxId = inventario.reduce((max, p) => p.id > max ? p.id : max, 0);
        nextId = maxId + 1;
    } 
    if (servData) {
        servicios = JSON.parse(servData);
        const maxServId = servicios.reduce((max, s) => s.id > max ? s.id : max, 0);
        nextServicioId = maxServId + 1;
    }

    // Cargar Caja, Historial y Facturas
    if (cajaData && cajaData !== 'null') { estadoCaja = JSON.parse(cajaData); }
    if (cortesData) { historialCortes = JSON.parse(cortesData); }
    if (ventasDiaData) { ventasDelDia = JSON.parse(ventasDiaData); }
    
    if (facturasData) {
        facturas = JSON.parse(facturasData);
        const maxFacturaId = facturas.reduce((max, f) => f.id > max ? f.id : max, 1000);
        nextFacturaId = maxFacturaId + 1;
    }


    // Si NO hay datos guardados, inicializar con EJEMPLOS
    if (!invData && !servData) {
        inventario = [
            { id: 1, nombre: 'Tornillos Autoperforantes 1"', clasificacion: 'Fijación', precioCompra: 0.10, precioVenta: 0.13, stockActual: 15, stockMinimo: 50, vencimiento: 'N/A'}, 
            { id: 2, nombre: 'Pintura Blanca Galón', clasificacion: 'Pinturas', precioCompra: 15.00, precioVenta: 19.50, stockActual: 100, stockMinimo: 10, vencimiento: '2025-11-20'}, 
            { id: 3, nombre: 'Martillo de Uña 16 Oz', clasificacion: 'Herramientas', precioCompra: 8.00, precioVenta: 10.40, stockActual: 0, stockMinimo: 5, vencimiento: 'N/A'},
        ];
        nextId = 4;
        
        servicios = [
            { id: 1, nombre: 'Servicio de Corte de Material', costoBase: 5.00, tarifaCliente: 10.00, duracion: 15 },
        ];
        nextServicioId = 2;
    }
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
// 3. GESTIÓN DE MÓDULO INVENTARIO (index.html)
// ==============================================================================

/** Listener para calcular el precio de venta mínimo (Precio Compra + 30%). */
const precioCompraInput = document.getElementById('precio-compra');
if (precioCompraInput) {
    precioCompraInput.addEventListener('input', function() {
        const precioCompra = parseFloat(this.value);
        const precioVentaInput = document.getElementById('precio-venta');
        if (isNaN(precioCompra) || precioCompra <= 0) { precioVentaInput.value = ''; return; }
        const precioVentaMinimo = precioCompra * (1 + MARGEN_MINIMO);
        precioVentaInput.value = precioVentaMinimo.toFixed(2);
    });
}

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
    
    const nuevoProducto = {
        id: nextId++,
        nombre: nombre,
        clasificacion: document.getElementById('clasificacion').value,
        precioCompra: precioCompra,
        precioVenta: precioVenta,
        stockActual: parseInt(document.getElementById('stock-actual').value) || 0,
        stockMinimo: parseInt(document.getElementById('stock-minimo').value) || 0,
        vencimiento: document.getElementById('fecha-vencimiento').value || 'N/A',
    };

    inventario.push(nuevoProducto);
    guardarDatos();
    actualizarTablaInventario();
    mostrarAlertaExito();
    actualizarTotalInvertido();
    
    const form = document.getElementById('form-inventario');
    if (form) form.reset();
}

/** Actualiza la tabla de productos con alertas visuales. */
function actualizarTablaInventario() {
    const tbody = document.getElementById('tabla-cuerpo');
    if (!tbody) return; 
    
    tbody.innerHTML = '';
    inventario.forEach(producto => {
        let claseFila = '';
        let claseStock = '';
        let claseVencimiento = '';

        if (producto.stockActual <= 0) { claseStock = 'stock-agotado'; claseFila = 'alerta-vencimiento'; }
        else if (producto.stockActual <= producto.stockMinimo || producto.stockActual <= ALERTA_STOCK_MINIMO) { claseStock = 'stock-bajo'; claseFila = 'alerta-stock'; }

        const fechaVenc = new Date(producto.vencimiento);
        const fechaLimite = new Date();
        fechaLimite.setMonth(fechaLimite.getMonth() + 1);
        const fechaTexto = producto.vencimiento !== 'N/A' ? new Date(producto.vencimiento).toISOString().split('T')[0] : 'N/A';

        if (producto.vencimiento !== 'N/A' && !isNaN(fechaVenc.getTime()) && fechaVenc < fechaLimite) {
            claseVencimiento = 'vencimiento-cerca';
            claseFila = 'alerta-vencimiento';
        }
        
        const fila = document.createElement('tr');
        fila.className = claseFila; 

        fila.innerHTML = `
            <td>${producto.id}</td>
            <td>${producto.nombre}</td>
            <td>${producto.clasificacion}</td>
            <td><span class="${claseStock}">${producto.stockActual}</span></td>
            <td>${producto.stockMinimo}</td>
            <td><span class="${claseVencimiento}">${fechaTexto}</span></td>
            <td>${producto.precioVenta.toFixed(2)}</td>
            <td class="acciones">
                <button class="btn-detalles">Detalles</button>
                <button class="btn-eliminar" onclick="eliminarProducto(${producto.id})">Eliminar</button>
            </td>
        `;
        tbody.appendChild(fila);
    });
}

/** Calcula y actualiza el valor total invertido en stock. */
function actualizarTotalInvertido() {
    const totalElement = document.getElementById('total-invertido');
    if (totalElement) {
        const total = inventario.reduce((sum, producto) => sum + (producto.precioCompra * producto.stockActual), 0);
        totalElement.textContent = total.toFixed(2);
    }
}

/** Elimina un producto del inventario. */
function eliminarProducto(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este artículo?')) {
        inventario = inventario.filter(producto => producto.id !== id);
        guardarDatos();
        actualizarTablaInventario();
        actualizarTotalInvertido();
    }
}


// ==============================================================================
// 4. GESTIÓN DE MÓDULO SERVICIOS (servicios.html)
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
    
    const nuevoServicio = {
        id: nextServicioId++,
        nombre: nombre,
        costoBase: costoBase,
        tarifaCliente: tarifaCliente,
        duracion: duracion,
    };

    servicios.push(nuevoServicio);
    guardarDatos();
    actualizarTablaServicios();
    mostrarAlertaExito();
}

/** Actualiza la tabla de servicios y los totales. */
function actualizarTablaServicios() {
    const tbody = document.getElementById('tabla-cuerpo-servicios');
    const totalServicios = document.getElementById('total-servicios');
    if (!tbody) return; 
    
    tbody.innerHTML = '';
    
    servicios.forEach(servicio => {
        const ganancia = servicio.tarifaCliente - servicio.costoBase;
        
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${servicio.id}</td>
            <td>${servicio.nombre}</td>
            <td>${servicio.costoBase.toFixed(2)}</td>
            <td>${servicio.tarifaCliente.toFixed(2)}</td>
            <td>${servicio.duracion} min</td>
            <td><span style="color: ${ganancia > 0 ? '#2ecc71' : '#e74c3c'}">${ganancia.toFixed(2)}</span></td>
            <td class="acciones">
                <button class="btn-detalles">Editar</button>
                <button class="btn-eliminar" onclick="eliminarServicio(${servicio.id})">Eliminar</button>
            </td>
        `;
        tbody.appendChild(fila);
    });
    
    if (totalServicios) totalServicios.textContent = servicios.length;
}

/** Elimina un servicio del listado. */
function eliminarServicio(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este servicio?')) {
        servicios = servicios.filter(s => s.id !== id);
        guardarDatos();
        actualizarTablaServicios();
    }
}


// ==============================================================================
// 5. GESTIÓN DE MÓDULO VENTAS (TPV - ventas.html)
// ==============================================================================

/** Busca productos (Inventario) y servicios. */
function buscarProducto() {
    const query = document.getElementById('input-busqueda').value.toLowerCase();
    const resultadosDiv = document.getElementById('resultado-busqueda');
    resultadosDiv.innerHTML = '';

    if (query.length < 2) return;

    const resultadosInv = inventario.filter(p => 
        (p.nombre.toLowerCase().includes(query) || p.id.toString() === query) && p.stockActual > 0
    ).map(p => ({ ...p, tipo: 'producto' }));
    
    const resultadosServ = servicios.filter(s => 
        s.nombre.toLowerCase().includes(query) || s.id.toString() === query
    ).map(s => ({ ...s, tipo: 'servicio', precioVenta: s.tarifaCliente })); 

    const resultados = [...resultadosInv, ...resultadosServ];
    
    resultados.forEach(p => {
        const item = document.createElement('div');
        item.className = 'resultado-item';
        const tipoEtiqueta = p.tipo === 'producto' ? 'P' : 'S';
        
        item.innerHTML = `
            <span>[${tipoEtiqueta}] ${p.nombre} - $${p.precioVenta.toFixed(2)}</span>
            <button onclick="agregarAlCarrito(${p.id}, '${p.tipo}')">Añadir</button>
        `;
        resultadosDiv.appendChild(item);
    });
    if (resultados.length === 0) {
        resultadosDiv.innerHTML = '<p style="padding: 10px; color: #e74c3c;">No se encontraron productos/servicios disponibles.</p>';
    }
}

/** Agrega un producto o servicio al carrito. */
function agregarAlCarrito(itemId, tipo) {
    let item;
    if (tipo === 'producto') {
        item = inventario.find(p => p.id === itemId);
    } else { 
        item = servicios.find(s => s.id === itemId);
        item.precioVenta = item.tarifaCliente; 
    }
    
    if (!item) return;

    const itemExistente = carrito.find(c => c.id === itemId && c.tipo === tipo);

    if (itemExistente) {
        if (tipo === 'producto' && itemExistente.cantidad >= item.stockActual) {
            alert('Stock insuficiente.');
            return;
        }
        itemExistente.cantidad++;
    } else {
        carrito.push({
            id: item.id,
            nombre: item.nombre,
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
}

function incrementarCantidad(itemId, tipo) {
    const itemExistente = carrito.find(c => c.id === itemId && c.tipo === tipo);
    if (itemExistente) {
        if (tipo === 'producto') {
            const productoInventario = inventario.find(p => p.id === itemId);
            if (itemExistente.cantidad >= productoInventario.stockActual) { alert('Stock insuficiente.'); return; }
        }
        itemExistente.cantidad++;
        actualizarCarritoDOM();
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

    // 1. Descontar Stock y Registrar Venta para Corte de Caja
    carrito.forEach(itemCarrito => {
        if (itemCarrito.tipo === 'producto') {
            const productoInventario = inventario.find(p => p.id === itemCarrito.id);
            if (productoInventario) {
                productoInventario.stockActual -= itemCarrito.cantidad;
            }
        }
    });
    
    // 2. REGISTRAR VENTA EN EFECTIVO PARA EL CORTE DE CAJA
    if (metodoPago === 'efectivo' && estadoCaja) {
        ventasDelDia.push({
            id: Date.now(),
            total: totalAPagar,
            fecha: new Date().toISOString()
        });
    }

    // 3. GENERAR FACTURA
    generarFactura(totalAPagar, montoRecibido, metodoPago);

    // 4. Guardar y limpiar
    guardarDatos(); 
    alert(`Venta finalizada con éxito. Factura #${nextFacturaId-1} generada. Su cambio: $${(montoRecibido - totalAPagar).toFixed(2)}`);

    carrito = [];
    montoRecibidoInput.value = '';
    actualizarCarritoDOM();
}

/** Cancela la venta actual y vacía el carrito. */
function cancelarVenta() {
    if (confirm('¿Deseas cancelar la venta actual y vaciar el carrito?')) {
        carrito = [];
        const montoRecibidoInput = document.getElementById('monto-recibido');
        if (montoRecibidoInput) montoRecibidoInput.value = '';
        actualizarCarritoDOM();
    }
}


// ==============================================================================
// 7. GESTIÓN DE MÓDULO FACTURAS (factura.html)
// ==============================================================================

/**
 * Genera el objeto factura y lo guarda en el array facturas.
 * @param {number} totalAPagar Total final de la venta.
 * @param {number} montoRecibido Monto entregado por el cliente.
 * @param {string} metodoPago Método de pago utilizado.
 */
function generarFactura(totalAPagar, montoRecibido, metodoPago) {
    const subtotal = totalAPagar / (1 + IVA_RATE);
    const impuestos = totalAPagar - subtotal;
    
    const nuevaFactura = {
        id: nextFacturaId++,
        fecha: new Date().toISOString(),
        cajero: USUARIO_VALIDO,
        items: JSON.parse(JSON.stringify(carrito)), // Copia profunda del carrito
        subtotal: subtotal,
        impuestos: impuestos,
        total: totalAPagar,
        montoRecibido: montoRecibido,
        cambio: montoRecibido - totalAPagar,
        metodoPago: metodoPago
    };

    facturas.push(nuevaFactura);
    guardarDatos();
    actualizarTablaFacturas();
}

/**
 * Actualiza la tabla con el historial de facturas generadas.
 */
function actualizarTablaFacturas() {
    const tbody = document.getElementById('tabla-cuerpo-facturas');
    const totalFacturas = document.getElementById('total-facturas');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    [...facturas].reverse().forEach(factura => { 
        const fecha = new Date(factura.fecha).toLocaleDateString() + ' ' + new Date(factura.fecha).toLocaleTimeString();
        
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>#${factura.id}</td>
            <td>${fecha}</td>
            <td>${factura.items.length}</td>
            <td>${factura.metodoPago.toUpperCase()}</td>
            <td>$${factura.total.toFixed(2)}</td>
            <td class="acciones">
                <button class="btn-detalles" onclick="verDetalleFactura(${factura.id})">Ver</button>
            </td>
        `;
        tbody.appendChild(fila);
    });
    
    if (totalFacturas) totalFacturas.textContent = facturas.length;
}

/**
 * Muestra el detalle de una factura específica en un modal o sección.
 * @param {number} facturaId ID de la factura a mostrar.
 */
function verDetalleFactura(facturaId) {
    const factura = facturas.find(f => f.id === facturaId);
    if (!factura) { alert('Factura no encontrada.'); return; }
    
    let detalleItems = factura.items.map(item => `
        <li>${item.cantidad} x ${item.nombre} @ $${item.precio.toFixed(2)} = $${(item.cantidad * item.precio).toFixed(2)}</li>
    `).join('');

    const modalContent = `
        <h3>Factura #${factura.id}</h3>
        <p><strong>Fecha:</strong> ${new Date(factura.fecha).toLocaleString()}</p>
        <p><strong>Cajero:</strong> ${factura.cajero}</p>
        <hr>
        <h4>Detalle de Ítems:</h4>
        <ul style="list-style-type: none; padding: 0;">${detalleItems}</ul>
        <hr>
        <p>Subtotal: <strong>$${factura.subtotal.toFixed(2)}</strong></p>
        <p>Impuestos (${(IVA_RATE * 100).toFixed(0)}%): <strong>$${factura.impuestos.toFixed(2)}</strong></p>
        <p style="font-size: 1.2em; font-weight: bold;">TOTAL: $${factura.total.toFixed(2)}</p>
        <p>Método de Pago: ${factura.metodoPago.toUpperCase()}</p>
        <p>Monto Recibido: $${factura.montoRecibido.toFixed(2)}</p>
        <p>Cambio: $${factura.cambio.toFixed(2)}</p>
    `;

    // Implementación simple de un modal de alerta (deberías usar un div modal en HTML)
    if (confirm("Factura generada con éxito.\n\n" + 
                `Total: $${factura.total.toFixed(2)}\n` + 
                `Cambio: $${factura.cambio.toFixed(2)}\n\n` + 
                "¿Desea ver el detalle completo en la consola?")) {
        console.log("Detalle de Factura #", factura.id, factura);
        alert("El detalle completo ha sido impreso en la Consola (F12 > Console).");
    }
}


// ==============================================================================
// 8. GESTIÓN DE MÓDULO ESTADO DE CAJA (estado_caja.html)
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
    if (estadoCaja) {
        alert('La caja ya está abierta. Debe cerrarla primero.');
        return;
    }

    estadoCaja = {
        fechaApertura: new Date().toISOString(),
        montoInicial: montoInicial,
        cajero: USUARIO_VALIDO 
    };
    ventasDelDia = []; 
    guardarDatos();
    actualizarVistaCaja();
    alert(`Caja abierta con éxito. Fondo inicial: $${montoInicial.toFixed(2)}`);
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
    
    const ventasEfectivoTotal = calcularTotalVentasEfectivo();
    const totalEsperado = estadoCaja.montoInicial + ventasEfectivoTotal;
    const diferencia = montoFinalRegistrado - totalEsperado;

    if (!confirm(`Confirmar Corte de Caja:\nEsperado: $${totalEsperado.toFixed(2)}\nFísico: $${montoFinalRegistrado.toFixed(2)}\nDiferencia: $${diferencia.toFixed(2)}\n¿Proceder?`)) {
        return;
    }

    const corteRegistro = {
        fechaApertura: estadoCaja.fechaApertura,
        fechaCierre: new Date().toISOString(),
        fondoInicial: estadoCaja.montoInicial,
        ventasEfectivo: ventasEfectivoTotal,
        montoFinalFisico: montoFinalRegistrado,
        diferencia: diferencia,
        cajero: estadoCaja.cajero
    };

    historialCortes.push(corteRegistro);
    estadoCaja = null; 
    ventasDelDia = []; 
    guardarDatos();
    actualizarVistaCaja();
    actualizarTablaCortes();
    alert('Corte de caja finalizado y registrado.');
}

/** Calcula el total de las ventas en efectivo. */
function calcularTotalVentasEfectivo() {
    return ventasDelDia.reduce((total, venta) => total + venta.total, 0);
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

        const ventasEfectivoTotal = calcularTotalVentasEfectivo();
        const totalEsperado = estadoCaja.montoInicial + ventasEfectivoTotal;

        document.getElementById('fecha-apertura-display').textContent = new Date(estadoCaja.fechaApertura).toLocaleTimeString();
        document.getElementById('caja-fondo').textContent = estadoCaja.montoInicial.toFixed(2);
        document.getElementById('ventas-efectivo').textContent = ventasEfectivoTotal.toFixed(2);
        document.getElementById('total-esperado').textContent = totalEsperado.toFixed(2);
        
        const montoFinalInput = document.getElementById('monto-final-registrado');
        if (montoFinalInput) { montoFinalInput.value = totalEsperado.toFixed(2); }

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

    tbody.innerHTML = '';
    
    [...historialCortes].reverse().forEach(corte => { 
        const fechaApertura = new Date(corte.fechaApertura).toLocaleDateString() + ' ' + new Date(corte.fechaApertura).toLocaleTimeString();
        
        let diferenciaClase = '';
        if (corte.diferencia === 0) { diferenciaClase = 'stock-bajo'; } 
        else if (corte.diferencia > 0) { diferenciaClase = 'alerta-stock'; } 
        else { diferenciaClase = 'alerta-vencimiento'; }

        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${fechaApertura}</td>
            <td>${corte.fondoInicial.toFixed(2)}</td>
            <td>${corte.ventasEfectivo.toFixed(2)}</td>
            <td>${corte.montoFinalFisico.toFixed(2)}</td>
            <td><span class="${diferenciaClase}">${corte.diferencia.toFixed(2)}</span></td>
            <td>${corte.cajero}</td>
        `;
        tbody.appendChild(fila);
    });
    
    if (cortesRegistrados) cortesRegistrados.textContent = historialCortes.length;
}


// ==============================================================================
// 9. 🚀 INICIALIZACIÓN DE LA APLICACIÓN (window.onload)
// ==============================================================================

/** Se ejecuta cuando la página ha terminado de cargar. */
window.onload = function() {
    const isAuth = checkAuthentication();

    if (isAuth) {
        cargarDatos();

        const pathname = window.location.pathname;

        if (pathname.endsWith('index.html')) {
            actualizarTablaInventario();
            actualizarTotalInvertido();
        } 
        else if (pathname.endsWith('servicios.html')) {
            actualizarTablaServicios();
        }
        else if (pathname.endsWith('ventas.html')) {
            actualizarCarritoDOM();
            const busquedaInput = document.getElementById('input-busqueda');
            if (busquedaInput) busquedaInput.addEventListener('input', buscarProducto);
        }
        else if (pathname.endsWith('estado_caja.html')) {
            actualizarVistaCaja();
            actualizarTablaCortes();
        }
        else if (pathname.endsWith('factura.html')) {
            actualizarTablaFacturas(); // ¡NUEVO! Inicializar historial de facturas
        }
    }
};