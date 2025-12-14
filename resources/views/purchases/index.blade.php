@extends('layouts.app')

@section('title', 'Compras | Sistema de Administración')

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
            <h3>NUEVA COMPRA</h3>
            
            <form id="form-compra" method="POST" action="{{ route('purchases.store') }}">
                @csrf
                
                @if($errors->any())
                    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                        {{ session('error') }}
                    </div>
                @endif
                
                <label for="proveedor">Proveedor:</label>
                <select id="proveedor" name="id_suppliers" required>
                    <option value="">Seleccionar proveedor</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('id_suppliers') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                
                <label for="fecha-compra">Fecha de Compra:</label>
                <input type="date" id="fecha-compra" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                
                <label for="numero-factura">Número de Factura:</label>
                <input type="text" id="numero-factura" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="Opcional" maxlength="255">
                
                <label for="notas">Notas:</label>
                <textarea id="notas" name="notes" rows="3" placeholder="Observaciones...">{{ old('notes') }}</textarea>
                
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #4a6681;">
                
                <h4 style="margin-bottom: 10px;">Productos</h4>
                
                <div id="productos-compra" style="margin-bottom: 15px;">
                    <!-- Los productos se agregarán aquí dinámicamente -->
                </div>
                
                <button type="button" class="btn-listo" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto" style="background-color: #3498db; margin-bottom: 10px;">
                    + Agregar Producto
                </button>
                
                <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                    <strong>Total:</strong> 
                    <span id="total-compra" style="font-size: 1.2em; color: #2c3e50;">$0.00</span>
                </div>
                
                <input type="hidden" id="total-amount" name="total_amount" value="0">
                
                <button type="submit" class="btn-listo" style="margin-top: 15px;">Registrar Compra</button>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Factura</th>
                    <th>Total</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->id }}</td>
                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->invoice_number ?? 'N/A' }}</td>
                        <td>${{ number_format($purchase->total_amount, 2) }}</td>
                        <td>{{ $purchase->user->name }}</td>
                        <td class="acciones">
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn-editar" style="background-color: #3498db; color: white; padding: 5px 10px; border-radius: 3px; margin-right: 5px; text-decoration: none;">Ver</a>
                            
                            <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" style="display: inline-block;" data-item-name="Compra #{{ $purchase->id }}" data-item-type="compra" data-warning-message="Esto revertirá el stock de los productos">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let productoIndex = 0;
const productosDisponibles = @json($products);

document.querySelector('button[data-bs-target="#modalAgregarProducto"]').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Generar opciones para el select
    let options = '<option value="">Seleccionar producto</option>';
    productosDisponibles.forEach(p => {
        options += `<option value="${p.id}">${p.name} (Stock: ${p.stock_initial})</option>`;
    });

    Swal.fire({
        title: '➕ Agregar Producto',
        html: `
            <div style="text-align: left;">
                <label style="display:block; margin-bottom:5px;">Producto</label>
                <select id="swal-producto" class="swal2-input" style="display:block; width:100%; margin: 0 0 15px 0;">
                    ${options}
                </select>
                
                <label style="display:block; margin-bottom:5px;">Cantidad</label>
                <input id="swal-cantidad" type="number" placeholder="Ej: 10" step="0.01" min="0.01" class="swal2-input" style="margin: 0 0 15px 0;">
                
                <label style="display:block; margin-bottom:5px;">Costo Unitario ($)</label>
                <input id="swal-costo" type="number" placeholder="Ej: 5.50" step="0.01" min="0" class="swal2-input" style="margin: 0 0 15px 0;">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3498db',
        focusConfirm: false,
        preConfirm: () => {
            const productoId = document.getElementById('swal-producto').value;
            const cantidad = document.getElementById('swal-cantidad').value;
            const costo = document.getElementById('swal-costo').value;

            if (!productoId || !cantidad || !costo) {
                Swal.showValidationMessage('Por favor completa todos los campos');
                return false;
            }
            
            return { productoId, cantidad, costo };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { productoId, cantidad, costo } = result.value;
            const producto = productosDisponibles.find(p => p.id == productoId);
            agregarProducto(productoId, producto.name, cantidad, costo);
            
            Swal.fire({
                icon: 'success',
                title: 'Producto agregado',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
});

function agregarProducto(productoId = null, productoNombre = '', cantidad = '', costo = '') {
    const container = document.getElementById('productos-compra');
    const div = document.createElement('div');
    div.className = 'producto-item';
    div.style.cssText = 'margin-bottom: 10px; padding: 10px; background-color: #ecf0f1; border-radius: 5px;';
    div.dataset.index = productoIndex;
    
    const nombreProducto = productoNombre || 'Producto';
    
    div.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <strong style="color: #2c3e50;">${nombreProducto}</strong>
            <button type="button" onclick="eliminarProducto(${productoIndex})" style="background-color: #e74c3c; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer;">✗</button>
        </div>
        <input type="hidden" name="items[${productoIndex}][product_id]" value="${productoId}">
        <div style="display: flex; gap: 5px;">
            <div style="flex: 1;">
                <label style="font-size: 0.85em; color: #555;">Cantidad:</label>
                <input type="number" name="items[${productoIndex}][quantity]" value="${cantidad}" step="0.01" min="0.01" required style="width: 100%; padding: 5px; color: #333;" onchange="calcularTotal()" readonly>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 0.85em; color: #555;">Costo Unit.:</label>
                <input type="number" name="items[${productoIndex}][unit_cost]" value="${costo}" step="0.01" min="0" required style="width: 100%; padding: 5px; color: #333;" onchange="calcularTotal()" readonly>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 0.85em; color: #555;">Subtotal:</label>
                <input type="text" value="$${(cantidad * costo).toFixed(2)}" style="width: 100%; padding: 5px; background-color: #d5dbdb; color: #333;" readonly>
            </div>
        </div>
    `;
    
    container.appendChild(div);
    productoIndex++;
    calcularTotal();
}

function eliminarProducto(index) {
    const item = document.querySelector(`[data-index="${index}"]`);
    if (item) {
        item.remove();
        calcularTotal();
    }
}

function calcularTotal() {
    let total = 0;
    const items = document.querySelectorAll('.producto-item');
    
    items.forEach(item => {
        const cantidad = parseFloat(item.querySelector('input[name*="[quantity]"]').value) || 0;
        const costo = parseFloat(item.querySelector('input[name*="[unit_cost]"]').value) || 0;
        total += cantidad * costo;
    });
    
    document.getElementById('total-compra').textContent = '$' + total.toFixed(2);
    document.getElementById('total-amount').value = total.toFixed(2);
}
</script>
@endpush
@endsection
