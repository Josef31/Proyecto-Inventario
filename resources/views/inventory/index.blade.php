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
                            {{-- 🚨 Botón Detalles: Placeholder (sin acción de edición) --}}
                            <button class="btn-detalles" onclick="alert('Mostrando detalles de {{ $product->name }}.')">Detalles</button> 
                            
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
                    <h4 style="color: #2c3e50; margin-bottom: 10px;">Columnas Requeridas:</h4>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                        <thead>
                            <tr style="background-color: #34495e; color: white;">
                                <th style="padding: 8px; border: 1px solid #ddd;">Columna</th>
                                <th style="padding: 8px; border: 1px solid #ddd;">Tipo</th>
                                <th style="padding: 8px; border: 1px solid #ddd;">Ejemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><strong>name</strong></td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Texto</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Laptop Dell</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><strong>id_classification</strong></td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Número (1-5)</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">1</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><strong>price_buy</strong></td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Decimal</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">850.50</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><strong>price_sell</strong></td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Decimal</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">1199.99</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><strong>stock_initial</strong></td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Entero</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">15</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><strong>stock_minimum</strong></td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Entero</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">5</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><strong>expiration_date</strong></td>
                                <td style="padding: 8px; border: 1px solid #ddd;">Fecha (opcional)</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">31/12/2028</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style="background-color: #e8f5e9; padding: 10px; border-radius: 5px; margin-top: 10px;">
                        <strong>⚠️ Importante:</strong>
                        <ul style="margin: 5px 0; padding-left: 20px;">
                            <li>La primera fila debe contener los nombres de las columnas</li>
                            <li>El precio de venta debe ser al menos 30% mayor al de compra</li>
                            <li>Las fechas deben estar en formato DD/MM/YYYY o dejar "N/A"</li>
                            <li>Archivo máximo: 5MB</li>
                        </ul>
                    </div>
                </div>
            `,
            confirmButtonText: '✅ Entendido',
            confirmButtonColor: '#27ae60',
            width: '700px'
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