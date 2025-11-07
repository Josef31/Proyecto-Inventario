@extends('layouts.app')

@section('title', 'Inventario | Sistema de Administración')

@section('content')
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil"></div>
            {{-- Usamos auth()->user() directamente si el layout.app ya lo tiene --}}
            <p class="nombre-usuario">{{ auth()->user()->name }}</p> 
            {{-- Asumiendo que el rol se obtiene de la sesión o del modelo User --}}
            <p class="rol-usuario">{{ auth()->user()->role ?? 'Administrador' }}</p>
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
                {{-- Se quita el atributo readonly para permitir la edición, si fuera necesario --}}
                <input type="number" id="precio-venta" name="price_sell" min="0" step="0.01" required> 
                
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
            {{-- Se usa la variable $totalInvested pasada desde el controlador --}}
            <p class="total-invertido">Total invertido en inventario: <span id="total-invertido">${{ number_format($totalInvested, 2) }}</span></p> 
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
                        // Lógica de Blade para las clases
                        $stockClass = '';
                        $rowClass = '';
                        $vencimientoClass = ''; // Nueva clase para la fecha

                        if ($product->stock_initial <= 0) {
                            $stockClass = 'stock-agotado';
                            $rowClass = 'alerta-vencimiento';
                        } elseif ($product->stock_initial <= $product->stock_minimum || $product->stock_initial <= 5) {
                            $stockClass = 'stock-bajo';
                            $rowClass = 'alerta-stock';
                        }
                        
                        $fechaTexto = 'N/A';
                        if ($product->expiration_date) {
                            $fechaVencimiento = \Carbon\Carbon::parse($product->expiration_date);
                            $fechaTexto = $fechaVencimiento->format('Y-m-d');
                            $unMesDespues = now()->addMonth();

                            if ($fechaVencimiento->isPast()) {
                                $rowClass = 'alerta-vencimiento'; // Vencido: Color rojo
                                $vencimientoClass = 'vencimiento-cerca';
                            } elseif ($fechaVencimiento <= $unMesDespues) {
                                $rowClass = $rowClass == 'alerta-vencimiento' ? 'alerta-vencimiento' : 'alerta-stock'; // Próximo a vencer: Color amarillo/rojo
                                $vencimientoClass = 'vencimiento-cerca';
                            }
                        }
                    @endphp
                    
                    <tr class="{{ $rowClass }}">
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->classification }}</td>
                        <td><span class="{{ $stockClass }}">{{ $product->stock_initial }}</span></td>
                        <td>{{ $product->stock_minimum }}</td>
                        <td><span class="{{ $vencimientoClass }}">{{ $fechaTexto }}</span></td>
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
@endsection

@section('scripts')
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

        // Botones de admin (Solo alertas en el front-end)
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

        // Nota: La validación de margen mínimo es mejor hacerla en el backend
        
        // Crear FormData para enviar
        const formData = {
            name: nombre,
            classification: clasificacion,
            price_buy: precioCompra,
            price_sell: precioVenta,
            stock_initial: stockInicial,
            stock_minimum: stockMinimo,
            expiration_date: fechaVencimiento,
            _token: '{{ csrf_token() }}'
        };

        // Enviar via AJAX 
        fetch('/inventory/store', {
            method: 'POST',
            body: JSON.stringify(formData), // Convertir a JSON
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                // Manejo de errores más robusto
                const errorMessage = data.errors ? Object.values(data.errors).join('\n') : (data.error || data.message || 'Error desconocido al guardar.');
                alert('Error al guardar el producto:\n' + errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar el producto. Verifique la conexión.');
        });
    }

    function eliminarProducto(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este artículo?')) {
            return;
        }

        fetch(`/inventory/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para actualizar la tabla
                window.location.reload();
            } else {
                alert('Error al eliminar: ' + (data.error || data.message || 'Error desconocido.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto. Verifique la conexión.');
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
@endsection