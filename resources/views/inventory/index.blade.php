<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | Sistema de Administración</title>
    <link rel="stylesheet" href="{{ asset('css/admin-system.css') }}">
</head>
<body>
    <div class="contenedor-principal">
        <header class="barra-superior">
            <nav>
                <a href="{{ route('inventory.index') }}">ADMINISTRACIÓN</a>
                <a href="{{ route('inventory.index') }}">Inventario</a>
                <a href="{{ url('/sales') }}">Ventas</a>
                <a href="{{ url('/services') }}">Servicios</a>
                <a href="{{ url('/cash') }}">Estado de caja</a>
                <a href="{{ url('/invoices') }}">Facturas</a>
                
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: #333; cursor: pointer; font-weight: bold;">
                        Cerrar Sesión ({{ $user['name'] }})
                    </button>
                </form>
            </nav>
        </header>

        <div class="contenido-flex">
            <aside class="barra-lateral">
                <div class="perfil">
                    <div class="icono-perfil"></div>
                    <p class="nombre-usuario">{{ $user['name'] }}</p>
                    <p class="rol-usuario">{{ $user['role'] }}</p>
                </div>

                <div class="seccion-formulario">
                    <h3>NUEVO ARTÍCULO</h3>
                    
                    <form id="form-producto">
                        @csrf
                        <label for="clasificacion">Clasificación / Talla:</label>
                        <select id="clasificacion" name="classification">
                            <option value="N/A">N/A</option>
                            <option value="Calzado">Calzado</option>
                            <option value="Vestido">Vestido</option>
                            <option value="S">Talla S</option>
                            <option value="Papelería">Papelería</option>
                            <option value="Herramientas">Herramientas</option>
                        </select>

                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="name" required>
                        
                        <label for="precio-compra">Precio Compra:</label>
                        <input type="number" id="precio-compra" name="price_buy" min="0" step="0.01" required>
                        
                        <label for="precio-venta">Precio Venta (Mínimo requerido):</label>
                        <input type="number" id="precio-venta" name="price_sell" min="0" step="0.01" readonly required>
                        
                        <label for="stock-actual">Stock Inicial:</label>
                        <input type="number" id="stock-actual" name="stock_initial" min="0" required>
                        
                        <label for="stock-minimo">Stock Mínimo (Alerta):</label>
                        <input type="number" id="stock-minimo" name="stock_minimum" min="0" required>

                        <label for="fecha-vencimiento">Fecha de Vencimiento:</label>
                        <input type="date" id="fecha-vencimiento" name="expiration_date">
                        
                        <button type="button" class="btn-listo" onclick="agregarProducto()">Listo</button>
                    </form>
                </div>

                <div class="seccion-acciones-admin">
                    <h3>ACCIONES ADMIN</h3>
                    <button class="btn-admin-accion btn-ajuste-stock">Ajuste de Inventario</button>
                    <button class="btn-admin-accion btn-autoconsumo">Ajuste de Autoconsumo</button>
                </div>
            </aside>

            <main class="seccion-inventario">
                <div class="cabecera-inventario">
                    <h2>Articulos en el Inventario</h2>
                    <div class="alerta-exito" id="alerta-exito" style="display: none;">
                        <span class="icono-ayuda">✓</span>
                        <p>Se creó el artículo con éxito</p>
                    </div>
                    <p class="total-invertido">Total invertido en inventario: <span id="total-invertido">{{ number_format($totalInvested, 2) }}</span></p>
                </div>

                <table class="tabla-inventario">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Clasif.</th>
                            <th>Existencias</th>
                            <th>Mínimo</th>
                            <th>Vencimiento</th>
                            <th>Precio Venta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-cuerpo">
                        @foreach($products as $product)
                            @php
                                $stockClass = '';
                                $rowClass = '';
                                
                                if ($product->stock_initial <= 0) {
                                    $stockClass = 'stock-agotado';
                                    $rowClass = 'alerta-vencimiento';
                                } elseif ($product->stock_initial <= $product->stock_minimum || $product->stock_initial <= 5) {
                                    $stockClass = 'stock-bajo';
                                    $rowClass = 'alerta-stock';
                                }
                                
                                $fechaTexto = $product->expiration_date ? \Carbon\Carbon::parse($product->expiration_date)->format('Y-m-d') : 'N/A';
                                
                                // Verificar si está próximo a vencer
                                if ($product->expiration_date) {
                                    $fechaVencimiento = \Carbon\Carbon::parse($product->expiration_date);
                                    $unMesDespues = now()->addMonth();
                                    if ($fechaVencimiento <= $unMesDespues) {
                                        $rowClass = 'alerta-vencimiento';
                                    }
                                }
                            @endphp
                            
                            <tr class="{{ $rowClass }}">
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->classification }}</td>
                                <td><span class="{{ $stockClass }}">{{ $product->stock_initial }}</span></td>
                                <td>{{ $product->stock_minimum }}</td>
                                <td>{{ $fechaTexto }}</td>
                                <td>${{ number_format($product->price_sell, 2) }}</td>
                                <td class="acciones">
                                    <button class="btn-detalles">Detalles</button>
                                    <button class="btn-eliminar" onclick="eliminarProducto({{ $product->id }})">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <script>
        // Calcular precio de venta mínimo automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const precioCompraInput = document.getElementById('precio-compra');
            const precioVentaInput = document.getElementById('precio-venta');
            
            if (precioCompraInput && precioVentaInput) {
                precioCompraInput.addEventListener('input', function() {
                    const precioCompra = parseFloat(this.value);
                    
                    if (isNaN(precioCompra) || precioCompra <= 0) {
                        precioVentaInput.value = '';
                        return;
                    }
                    
                    const precioVentaMinimo = precioCompra * 1.3; // 30% de margen
                    precioVentaInput.value = precioVentaMinimo.toFixed(2);
                });
            }

            // Botones de admin
            const btnAjusteStock = document.querySelector('.btn-ajuste-stock');
            const btnAutoconsumo = document.querySelector('.btn-autoconsumo');
            
            if (btnAjusteStock) {
                btnAjusteStock.addEventListener('click', function() {
                    alert('Funcionalidad de Ajuste de Inventario - En desarrollo');
                });
            }
            
            if (btnAutoconsumo) {
                btnAutoconsumo.addEventListener('click', function() {
                    alert('Funcionalidad de Ajuste de Autoconsumo - En desarrollo');
                });
            }
        });

        function agregarProducto() {
            const nombre = document.getElementById('nombre').value.trim();
            const precioCompra = parseFloat(document.getElementById('precio-compra').value);
            const precioVenta = parseFloat(document.getElementById('precio-venta').value);
            const stockInicial = parseInt(document.getElementById('stock-actual').value) || 0;
            const stockMinimo = parseInt(document.getElementById('stock-minimo').value) || 0;
            const clasificacion = document.getElementById('clasificacion').value;
            const fechaVencimiento = document.getElementById('fecha-vencimiento').value;
            
            if (nombre === '' || isNaN(precioCompra) || isNaN(precioVenta)) {
                alert('Por favor, rellena todos los campos de forma correcta.');
                return;
            }

            if (precioVenta < (precioCompra * 1.3 - 0.01)) {
                alert('Error: El precio de venta no cumple con el margen mínimo del 30% requerido.');
                return;
            }

            // Crear FormData para enviar
            const formData = new FormData();
            formData.append('name', nombre);
            formData.append('classification', clasificacion);
            formData.append('price_buy', precioCompra);
            formData.append('price_sell', precioVenta);
            formData.append('stock_initial', stockInicial);
            formData.append('stock_minimum', stockMinimo);
            formData.append('expiration_date', fechaVencimiento);
            formData.append('_token', '{{ csrf_token() }}');

            // Enviar via AJAX - CORREGIDO: usar URL directa en lugar de route()
            fetch('/inventory/store', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlertaExito();
                    limpiarFormulario();
                    // Recargar la página para mostrar el nuevo producto
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al agregar el producto');
            });
        }

        function eliminarProducto(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar este artículo?')) {
                return;
            }

            // CORREGIDO: usar URL directa en lugar de route()
            fetch(`/inventory/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para actualizar la tabla
                    window.location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el producto');
            });
        }

        function limpiarFormulario() {
            document.getElementById('nombre').value = '';
            document.getElementById('precio-compra').value = '';
            document.getElementById('precio-venta').value = '';
            document.getElementById('stock-actual').value = '';
            document.getElementById('stock-minimo').value = '';
            document.getElementById('fecha-vencimiento').value = '';
            document.getElementById('clasificacion').value = 'N/A';
        }

        function mostrarAlertaExito() {
            const alerta = document.getElementById('alerta-exito');
            if (alerta) {
                alerta.style.display = 'flex';
                setTimeout(() => { 
                    alerta.style.display = 'none'; 
                }, 3000);
            }
        }
    </script>
</body>
</html>