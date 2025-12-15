@extends('layouts.app')

@section('title', 'Inventario | Sistema de Administración')

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
            <h3>NUEVO ARTÍCULO</h3>
            
            <form id="form-producto" method="POST" action="{{ route('inventory.store') }}">
                @csrf
                
                <label for="clasificacion">Clasificación / Talla:</label>
                <select id="clasificacion" name="id_classification" required>
                    <option value="">Seleccione una clasificación</option>
                    @foreach($classifications as $classification)
                        <option value="{{ $classification->id }}" {{ old('id_classification') == $classification->id ? 'selected' : '' }}>
                            {{ $classification->name }}
                        </option>
                    @endforeach
                </select>

                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="name" value="{{ old('name') }}" required>
                
                <label for="precio-compra">Precio Compra:</label>
                <input type="number" id="precio-compra" name="price_buy" min="0" step="0.01" value="{{ old('price_buy') }}" required>
                
                <label for="precio-venta">Precio Venta (Mínimo requerido):</label>
                <input type="number" id="precio-venta" name="price_sell" min="0" step="0.01" value="{{ old('price_sell') }}" required> 
                
                <label for="stock-actual">Stock Inicial:</label>
                <input type="number" id="stock-actual" name="stock_initial" min="0" value="{{ old('stock_initial') }}" required>
                
                <label for="stock-minimo">Stock Mínimo (Alerta):</label>
                <input type="number" id="stock-minimo" name="stock_minimum" min="0" value="{{ old('stock_minimum') }}" required>

                <label for="fecha-vencimiento">Fecha de Vencimiento:</label>
                <input type="date" id="fecha-vencimiento" name="expiration_date" value="{{ old('expiration_date') }}">
                
                <button type="submit" class="btn-listo">Listo</button>
            </form>
        </div>

        {{-- 🚨 CORRECCIÓN: Estilo para centrar el botón --}}
        <div class="seccion-acciones-admin" style="text-align: center;"> 
            <h3>ACCIONES ADMIN</h3>
            {{-- Botón Importar Datos Masivamente --}}
            <button type="button" class="btn-admin-accion btn-ajuste-stock" onclick="mostrarModalImportacion()">
                📊 Importar Datos Masivamente
            </button>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Articulos en el Inventario</h2>
            
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
                        // Asumiendo que estos Accessors existen en Product.php
                        $stockClass = $product->getStockClassAttribute();
                        $rowClass = $product->getRowClassAttribute();
                        $vencimientoClass = $product->getIsNearExpirationAttribute() ? 'vencimiento-cerca' : '';
                        $fechaTexto = $product->getFormattedExpirationDateAttribute();
                    @endphp
                    
                    <tr class="{{ $rowClass }}">
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->classification_name }}</td>
                        <td><span class="{{ $stockClass }}">{{ $product->stock_initial }}</span></td>
                        <td>{{ $product->stock_minimum }}</td>
                        <td><span class="{{ $vencimientoClass }}">{{ $fechaTexto }}</span></td>
                        <td>${{ number_format($product->price_sell, 2) }}</td>
                        <td class="acciones">
                            {{-- 🚨 Botón Detalles: Evento delegado JS --}}
                            <button type="button" class="btn-detalles btn-ver-historial" data-id="{{ $product->id }}">Detalles</button> 
                            
                            {{-- 🚨 Botón Editar: Dirige a la edición individual --}}
                            <a href="{{ route('inventory.edit', $product->id) }}" class="btn-editar" style="background-color: #039438; color: white; padding: 5px 10px; border-radius: 3px; margin-right: 5px; text-decoration: none;">Editar</a>
                            
                            {{-- Botón Eliminar --}}
                            <form action="{{ route('inventory.destroy', $product->id) }}" method="POST" style="display: inline-block;" data-item-name="{{ $product->name }}" data-item-type="producto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>
@endsection

@push('scripts')
<script>
    // ========================================
    // FUNCIONES PARA IMPORTACIÓN MASIVA (GLOBALES)
    // ========================================
    
    function mostrarModalImportacion() {
        Swal.fire({
            title: '📊 Importar Datos Masivamente',
            html: `
                <p style="margin-bottom: 20px;">Selecciona una opción:</p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button onclick="mostrarInstrucciones()" class="swal2-confirm swal2-styled" style="background-color: #3498db;">
                        📋 Ver Instrucciones
                    </button>
                    <button onclick="mostrarUpload()" class="swal2-confirm swal2-styled" style="background-color: #27ae60;">
                        📤 Subir Excel
                    </button>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            width: '500px'
        });
    }
    
    function mostrarInstrucciones() {
        Swal.fire({
            title: '📋 Formato del Archivo Excel',
            html: `
                <div style="text-align: left; padding: 10px;">
                    <p style="margin-bottom: 15px; color: #555;">Tu archivo Excel debe tener las siguientes columnas en la primera fila:</p>
                    
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px;">
                        <thead>
                            <tr style="background-color: #5b9bd5; color: white;">
                                <th style="padding: 10px; border: 1px solid #ddd;">name</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">id_classification</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">price_buy</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">price_sell</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">stock_initial</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">stock_minimum</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">expiration_date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background-color: #f9f9f9;">
                                <td style="padding: 8px; border: 1px solid #ddd; font-style: italic; color: #666;">Laptop Dell</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">1</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">850.50</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">1199.99</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">15</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">5</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">31/12/2028</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style="background-color: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; margin-bottom: 10px;">
                        <strong>📌 Tipos de datos:</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px; font-size: 13px;">
                            <li><strong>name:</strong> Texto (nombre del producto)</li>
                            <li><strong>id_classification:</strong> Número del 1 al 5</li>
                            <li><strong>price_buy:</strong> Número decimal (precio de compra)</li>
                            <li><strong>price_sell:</strong> Número decimal (precio de venta)</li>
                            <li><strong>stock_initial:</strong> Número entero (stock inicial)</li>
                            <li><strong>stock_minimum:</strong> Número entero (stock mínimo)</li>
                            <li><strong>expiration_date:</strong> Fecha DD/MM/YYYY o "N/A"</li>
                        </ul>
                    </div>
                    
                    <div style="background-color: #d1ecf1; padding: 12px; border-left: 4px solid #0c5460; margin-bottom: 10px;">
                        <strong>🏷️ Clasificaciones (id_classification):</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px; font-size: 13px;">
                            <li><strong>1</strong> = Electrónica</li>
                            <li><strong>2</strong> = Alimentos</li>
                            <li><strong>3</strong> = Ropa</li>
                            <li><strong>4</strong> = Servicios</li>
                            <li><strong>5</strong> = Otros</li>
                        </ul>
                    </div>
                    
                    <div style="background-color: #f8d7da; padding: 12px; border-left: 4px solid #721c24;">
                        <strong>⚠️ Reglas importantes:</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px; font-size: 13px;">
                            <li>La primera fila debe tener los nombres de columnas exactos</li>
                            <li>El precio de venta debe ser <strong>mínimo 30% mayor</strong> al de compra</li>
                            <li>Formato de archivo: <strong>.xlsx</strong> o <strong>.xls</strong></li>
                            <li>Tamaño máximo: <strong>5MB</strong></li>
                        </ul>
                    </div>
                </div>
            `,
            confirmButtonText: '✅ Entendido',
            confirmButtonColor: '#27ae60',
            width: '900px'
        });
    }
    
    function mostrarUpload() {
        Swal.fire({
            title: '📤 Subir Archivo Excel',
            html: `
                <form id="form-import-excel" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div style="margin: 20px 0;">
                        <label for="excel-file" style="display: block; margin-bottom: 10px; font-weight: bold;">
                            Selecciona tu archivo Excel:
                        </label>
                        <input type="file" 
                               id="excel-file" 
                               name="excel_file" 
                               accept=".xlsx,.xls" 
                               required
                               style="padding: 10px; border: 2px dashed #3498db; border-radius: 5px; width: 100%;">
                        <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                            Formatos aceptados: .xlsx, .xls (Máx. 5MB)
                        </small>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: '📥 Importar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#27ae60',
            preConfirm: () => {
                const fileInput = document.getElementById('excel-file');
                const file = fileInput.files[0];
                
                if (!file) {
                    Swal.showValidationMessage('Por favor selecciona un archivo');
                    return false;
                }
                
                if (file.size > 5 * 1024 * 1024) {
                    Swal.showValidationMessage('El archivo no debe superar 5MB');
                    return false;
                }
                
                return file;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                procesarImportacion(result.value);
            }
        });
    }
    
    function procesarImportacion(file) {
        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        Swal.fire({
            title: 'Procesando...',
            html: 'Importando productos, por favor espera...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('{{ route("inventory.import") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Importación Exitosa!',
                    html: `
                        <p><strong>${data.imported}</strong> productos importados correctamente</p>
                        ${data.errors && data.errors.length > 0 ? 
                            `<p style="color: #e74c3c; margin-top: 10px;">
                                <strong>${data.errors.length}</strong> filas con errores (omitidas)
                            </p>` : ''}
                    `,
                    confirmButtonColor: '#27ae60'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la Importación',
                    text: data.message || 'Ocurrió un error al procesar el archivo',
                    confirmButtonColor: '#e74c3c'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud',
                confirmButtonColor: '#e74c3c'
            });
        });
    }

    // ========================================
    // HISTORIAL DE MOVIMIENTOS
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ JS Inventario cargado correctamente');
        
        // Event Delegation para el botón de historial
        document.body.addEventListener('click', function(e) {
            // Buscar el botón clickeado (o su padre si se clickeó un icono interno)
            const btn = e.target.closest('.btn-ver-historial');
            
            if (btn) {
                console.log('🖱️ Click detectado en botón historial');
                const id = btn.getAttribute('data-id');
                if (id) {
                    verHistorial(id);
                } else {
                    console.error('❌ Error: El botón no tiene ID');
                }
            }
        });
    });

    function verHistorial(id) {
        console.log('📖 Cargando historial para ID:', id);
        
        if (typeof Swal === 'undefined') {
            alert('Error: SweetAlert2 no está cargado. Recarga la página.');
            return;
        }

        Swal.fire({
            title: 'Cargando historial...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch data
        fetch(`{{ url('/inventory') }}/${id}/history`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Error desconocido');
                }

                // Build HTML Table
                let html = `
                    <div style="text-align: left;">
                        <h4 style="color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                            ${data.product} <span style="font-size: 0.6em; color: #7f8c8d;">(Historial de Movimientos)</span>
                        </h4>
                        
                        <div style="max-height: 400px; overflow-y: auto; overflow-x: hidden; border: 1px solid #eee; border-radius: 4px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px; min-width: 600px;">
                                <thead style="position: sticky; top: 0; z-index: 1;">
                                    <tr style="background-color: #34495e; color: white; text-align: left;">
                                        <th style="padding: 10px; border-radius: 0;">Fecha</th>
                                        <th style="padding: 10px;">Tipo</th>
                                        <th style="padding: 10px; text-align: center;">Cant.</th>
                                        <th style="padding: 10px; text-align: right;">Total</th>
                                        <th style="padding: 10px; border-radius: 0;">Detalle / Referencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                if (data.movements.length === 0) {
                    html += `
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: #7f8c8d; background-color: #f9f9f9;">
                                <i>No hay movimientos registrados para este producto.</i>
                            </td>
                        </tr>
                    `;
                } else {
                    data.movements.forEach(mov => {
                        let badgeColor, signo, typeClass;
                        
                        if (mov.type === 'Compra') {
                            badgeColor = '#3498db'; // Azul
                            signo = '+';
                        } else if (mov.type === 'Venta') {
                            badgeColor = '#27ae60'; // Verde
                            signo = '-';
                        } else {
                            badgeColor = '#e67e22'; // Naranja
                            signo = '-';
                        }
                        
                        // Format money (handling varied inputs safely)
                        const totalVal = parseFloat(mov.total) || 0;
                        const totalFormatted = '$' + totalVal.toFixed(2);

                        html += `
                            <tr style="border-bottom: 1px solid #ecf0f1;">
                                <td style="padding: 10px;">${mov.date}</td>
                                <td style="padding: 10px;">
                                    <span style="background-color: ${badgeColor}; color: white; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                        ${mov.type.toUpperCase()}
                                    </span>
                                </td>
                                <td style="padding: 10px; text-align: center; font-weight: bold; color: ${signo === '+' ? '#27ae60' : '#c0392b'};">
                                    ${signo}${Math.abs(mov.quantity)}
                                </td>
                                <td style="padding: 10px; text-align: right;">${totalFormatted}</td>
                                <td style="padding: 10px; color: #555;">
                                    <strong>${mov.reference}</strong><br>
                                    <small>${mov.detail}</small>
                                </td>
                            </tr>
                        `;
                    });
                }

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                Swal.fire({
                    html: html,
                    width: '800px', // Wider modal
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#95a5a6'
                });
            })
            .catch(error => {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el historial: ' + error.message,
                    confirmButtonColor: '#e74c3c'
                });
            });
    }
    
    // ========================================
    // OTRAS FUNCIONES
    // ========================================
    
    // Cálculo local del precio de venta mínimo (UX)
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
                
                const precioVentaMinimo = precioCompra * 1.3;
                precioVentaInput.value = precioVentaMinimo.toFixed(2);
            });
        }
    });
</script>
@endpush