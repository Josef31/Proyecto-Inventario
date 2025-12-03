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
                <input type="date" id="fecha-compra" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                
                <label for="numero-factura">Número de Factura:</label>
                <input type="text" id="numero-factura" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="Opcional" maxlength="255">
                
                <label for="notas">Notas:</label>
                <textarea id="notas" name="notes" rows="3" placeholder="Observaciones...">{{ old('notes') }}</textarea>
                
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #4a6681;">
                
                <h4 style="margin-bottom: 10px;">Productos</h4>
                
                <div id="productos-compra" style="margin-bottom: 15px;">
                    <!-- Los productos se agregarán aquí dinámicamente -->
                </div>
                
                <button type="button" id="btn-agregar-producto" class="btn-listo" style="background-color: #3498db; margin-bottom: 10px;">
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
<script>
let productoIndex = 0;
const productosDisponibles = @json($products);

document.getElementById('btn-agregar-producto').addEventListener('click', function() {
    agregarProducto();
});

function agregarProducto() {
    const container = document.getElementById('productos-compra');
    const div = document.createElement('div');
    div.className = 'producto-item';
    div.style.cssText = 'margin-bottom: 10px; padding: 10px; background-color: #ecf0f1; border-radius: 5px;';
    div.dataset.index = productoIndex;
    
    div.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <strong style="color: #2c3e50;">Producto ${productoIndex + 1}</strong>
            <button type="button" onclick="eliminarProducto(${productoIndex})" style="background-color: #e74c3c; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer;">✗</button>
        </div>
        <select name="items[${productoIndex}][product_id]" required style="width: 100%; margin-bottom: 5px; padding: 5px; color: #333;">
            <option value="">Seleccionar producto</option>
            ${productosDisponibles.map(p => `<option value="${p.id}">${p.name} (Stock: ${p.stock_initial})</option>`).join('')}
        </select>
        <div style="display: flex; gap: 5px;">
            <input type="number" name="items[${productoIndex}][quantity]" placeholder="Cantidad" step="0.01" min="0.01" required style="flex: 1; padding: 5px; color: #333;" onchange="calcularTotal()">
            <input type="number" name="items[${productoIndex}][unit_cost]" placeholder="Costo Unit." step="0.01" min="0" required style="flex: 1; padding: 5px; color: #333;" onchange="calcularTotal()">
        </div>
    `;
    
    container.appendChild(div);
    productoIndex++;
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

// Agregar un producto por defecto
agregarProducto();
</script>
@endpush
@endsection
