// ==============================================================================
// 0. CONFIGURACIÓN Y ESTADOS GLOBALES (Mínimo necesario para la UI)
// ==============================================================================

/** Array para mantener el estado de la venta actual en el navegador. */
let carrito = [];          

// --- Constantes de Reglas de Negocio (solo para cálculos de UI inmediatos) ---
// NOTA: El cálculo final del IVA y los totales debe ser RE-VALIDADO por Laravel
const IVA_RATE = 0.16;               

// ==============================================================================
// 1. UTILIDADES Y VISTAS
// ==============================================================================

/** Muestra la alerta verde de éxito (Se mantiene para feedback rápido). */
function mostrarAlertaExito() {
    const alerta = document.getElementById('alerta-exito');
    if (alerta) {
        alerta.style.display = 'flex';
        setTimeout(() => { alerta.style.display = 'none'; }, 3000);
    }
}

// ==============================================================================
// 2. GESTIÓN DE MÓDULO INVENTARIO (Se mantiene solo la interacción con la API)
// ==============================================================================

// Listener para calcular el precio de venta mínimo (Se mantiene para feedback en el formulario)
const MARGEN_MINIMO = 0.30; 
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
            // Muestra el valor MÍNIMO, pero el input de precio-venta sigue siendo editable
            document.getElementById('precio-venta-minimo').textContent = precioVentaMinimo.toFixed(2);
        });
    }
});

/** Agrega un producto de inventario (Llama a la API de Laravel). */
function agregarProducto() {
    // Lógica de validación básica en JS antes de enviar a Laravel
    const nombre = document.getElementById('nombre').value.trim();
    const precioCompra = parseFloat(document.getElementById('precio-compra').value);
    const precioVenta = parseFloat(document.getElementById('precio-venta').value);
    
    if (nombre === '' || isNaN(precioCompra) || isNaN(precioVenta)) {
        alert('Por favor, rellena todos los campos de forma correcta.');
        return;
    }
    
    // La validación del margen mínimo se debe hacer en el Controller de Laravel
    
    const formData = new FormData(document.getElementById('form-inventario')); // Simplificando
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
        // Redirige o recarga la tabla para reflejar el cambio (Laravel refresca la vista)
        window.location.reload(); 
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el producto');
    });
}

/** Elimina un producto del inventario (Llama a la API de Laravel). */
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
        // Redirige o recarga la tabla para reflejar el cambio (Laravel refresca la vista)
        window.location.reload(); 
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el producto');
    });
}

// ==============================================================================
// 3. GESTIÓN DE MÓDULO SERVICIOS (Se mantiene solo la interacción con la API)
// ==============================================================================

/** Agrega un nuevo servicio (Llama a la API de Laravel). */
function agregarServicio() {
    // Lógica de validación básica en JS antes de enviar a Laravel
    const nombre = document.getElementById('nombre-servicio').value.trim();
    const costoBase = parseFloat(document.getElementById('costo-base').value);
    const tarifaCliente = parseFloat(document.getElementById('tarifa-cliente').value);

    if (nombre === '' || isNaN(costoBase) || isNaN(tarifaCliente)) {
        alert('Por favor, rellena todos los campos del servicio.');
        return;
    }
    
    const formData = new FormData(document.getElementById('form-servicios'));
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/api/services', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        mostrarAlertaExito();
        // Redirige o recarga la tabla para reflejar el cambio (Laravel refresca la vista)
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el servicio');
    });
}

/** Elimina un servicio (Llama a la API de Laravel). */
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
        // Redirige o recarga la tabla para reflejar el cambio (Laravel refresca la vista)
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el servicio');
    });
}

// ==============================================================================
// 4. GESTIÓN DE MÓDULO VENTAS (TPV) - Lógica de Carrito
// ==============================================================================

/**
 * Función clave para añadir un producto/servicio al carrito.
 * Se asume que esta función es llamada por el evento 'onchange' del <select>
 * o de un botón 'Añadir' en el caso de la búsqueda.
 */
function seleccionarProducto(selectElement) {
    // Si se llama desde el onchange de un <select>
    const value = selectElement ? selectElement.value : document.getElementById('select-producto').value;
    
    // El valor del <option> debe ser un JSON.stringify(producto) o un ID único.
    // Por simplicidad, asumimos que el 'value' es el JSON completo del producto/servicio.
    
    if (!value) return;

    try {
        // Se espera que el value del <select> sea un JSON válido
        const itemData = JSON.parse(value); 
        
        // Determinar el ID del item y el tipo
        const itemId = itemData.id;
        // Si tiene 'stock' y 'price_sell' es un producto, si tiene 'client_rate' es un servicio.
        const tipo = itemData.price_sell ? 'producto' : 'servicio';
        const precio = itemData.price_sell ? parseFloat(itemData.price_sell) : parseFloat(itemData.client_rate);
        const nombre = itemData.name;
        const stock = itemData.stock; // Solo para productos
        
        const itemExistente = carrito.find(c => c.id === itemId && c.tipo === tipo);

        if (itemExistente) {
            if (tipo === 'producto' && itemExistente.cantidad >= stock) {
                alert('Stock insuficiente.');
                return;
            }
            itemExistente.cantidad++;
        } else {
            carrito.push({
                id: itemId,
                nombre: nombre,
                precio: precio,
                cantidad: 1,
                tipo: tipo,
                // Se mantiene el stock en el item para validación rápida al incrementar
                stock: stock 
            });
        }

        // Restablecer el select a la opción por defecto después de agregar
        if (selectElement) selectElement.value = '';
        
        actualizarCarritoDOM();

    } catch (e) {
        // Si hay un SyntaxError: JSON.parse, es porque el 'value' no es JSON
        console.error("Error al parsear producto/servicio: ", e);
    }
}


/** Recalcula totales y actualiza la tabla del carrito (Solo DOM). */
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

// Lógica de incremento/decremento/eliminación del carrito (Se mantiene, usa la variable 'carrito' global)
function incrementarCantidad(itemId, tipo) {
    const itemExistente = carrito.find(c => c.id === itemId && c.tipo === tipo);
    if (itemExistente) {
        // Solo valida contra el stock guardado localmente (para evitar otra llamada a API)
        if (tipo === 'producto' && itemExistente.cantidad >= itemExistente.stock) {
            alert('Stock insuficiente.');
            return;
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

/** Procesa el pago, enviando el carrito completo a Laravel. */
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
        // Enviar el carrito, el total que calculó el cliente y los detalles de pago.
        items: carrito,
        total: totalAPagar,
        monto_recibido: montoRecibido,
        metodo_pago: metodoPago
    };

    // NOTA: Usar fetch('/api/sales') o fetch('{{ route('sales.process') }}')
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
        
        // Recargar la página si se necesita actualizar el select de productos
        // window.location.reload();
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
    }
}


// ==============================================================================
// 5. GESTIÓN DE FACTURAS (Se mantiene solo la interacción con la API)
// ==============================================================================

/** Se asume que la tabla de facturas es cargada por Laravel al renderizar la vista. */

/** Muestra el detalle de una factura específica. */
function verDetalleFactura(facturaId) {
    fetch(`/api/invoices/${facturaId}`)
    .then(response => response.json())
    .then(factura => {
        // Se mantiene la lógica de modal simple para mostrar el detalle
        let detalleItems = factura.items.map(item => `
            <li>${item.cantidad} x ${item.nombre} @ $${parseFloat(item.precio).toFixed(2)} = $${(item.cantidad * item.precio).toFixed(2)}</li>
        `).join('');

        const modalContent = `
            <h3>Factura #${factura.id}</h3>
            <p><strong>Fecha:</strong> ${new Date(factura.created_at).toLocaleString()}</p>
            <p><strong>Cajero:</strong> ${factura.cajero || 'N/A'}</p>
            <hr>
            <h4>Detalle de Ítems:</h4>
            <ul style="list-style-type: none; padding: 0;">${detalleItems}</ul>
            <hr>
            <p>Subtotal: <strong>$${parseFloat(factura.subtotal).toFixed(2)}</strong></p>
            <p>Impuestos (${(IVA_RATE * 100).toFixed(0)}%): <strong>$${parseFloat(factura.impuestos).toFixed(2)}</strong></p>
            <p style="font-size: 1.2em; font-weight: bold;">TOTAL: $${parseFloat(factura.total).toFixed(2)}</p>
            <p>Método de Pago: ${factura.metodo_pago ? factura.metodo_pago.toUpperCase() : 'N/A'}</p>
            <p>Monto Recibido: $${parseFloat(factura.monto_recibido).toFixed(2)}</p>
            <p>Cambio: $${parseFloat(factura.cambio).toFixed(2)}</p>
        `;

        // Lógica de modal simple (Mantenida por ser puramente de la UI)
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
// 6. GESTIÓN DE ESTADO DE CAJA (Se mantiene solo la interacción con la API)
// ==============================================================================

/** Abre la caja (Llama a la API de Laravel). */
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
        
        alert(`Caja abierta con éxito. Fondo inicial: $${montoInicial.toFixed(2)}`);
        // Recargar la página para que Laravel renderice la vista de Cierre
        window.location.reload(); 
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al abrir la caja');
    });
}

/** Cierra la caja (Llama a la API de Laravel). */
function cerrarCaja() {
    // No hay chequeo de estadoCaja local, se delega a Laravel

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
        
        alert('Corte de caja finalizado y registrado.');
        // Recargar la página para que Laravel renderice la vista de Apertura
        window.location.reload(); 
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cerrar la caja');
    });
}

// ==============================================================================
// 7. INICIALIZACIÓN DE LA APLICACIÓN
// ==============================================================================

/** Se ejecuta cuando la página ha terminado de cargar. */
document.addEventListener('DOMContentLoaded', function() {
    // Si estamos en la vista de ventas (TPV), actualiza el carrito local
    const pathname = window.location.pathname;

    if (pathname.includes('sales')) {
        // Se mantiene para que el carrito se renderice si la vista se cargó
        actualizarCarritoDOM(); 
        
        // Agregar listener al input de Monto Recibido para calcular el cambio en la UI
        const montoRecibidoInput = document.getElementById('monto-recibido');
        if (montoRecibidoInput) {
            montoRecibidoInput.addEventListener('input', actualizarCarritoDOM);
        }
    }

});