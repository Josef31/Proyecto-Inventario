@extends('layouts.app')

@section('title', 'Consumos | Sistema de Administración')

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
            <h3>NUEVO CONSUMO</h3>
            
            <form id="form-consumo" method="POST" action="{{ route('consumption.store') }}">
                @csrf
                
                <label for="producto">Producto:</label>
                <select id="producto" name="product_id" required>
                    <option value="">Seleccionar producto</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $product->stock_initial }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} (Stock: {{ $product->stock_initial }})
                        </option>
                    @endforeach
                </select>
                
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="quantity" value="{{ old('quantity') }}" placeholder="Ej: 5" step="0.01" min="0.01" required>
                <small id="stock-disponible" style="color: #666; font-size: 0.85em; display: block; margin-top: -8px; margin-bottom: 10px;"></small>
                
                <label for="motivo">Motivo:</label>
                <select id="motivo" name="reason" required>
                    <option value="">Seleccionar motivo</option>
                    <option value="Uso interno" {{ old('reason') == 'Uso interno' ? 'selected' : '' }}>Uso interno</option>
                    <option value="Producto dañado" {{ old('reason') == 'Producto dañado' ? 'selected' : '' }}>Producto dañado</option>
                    <option value="Producto vencido" {{ old('reason') == 'Producto vencido' ? 'selected' : '' }}>Producto vencido</option>
                    <option value="Muestra/Degustación" {{ old('reason') == 'Muestra/Degustación' ? 'selected' : '' }}>Muestra/Degustación</option>
                    <option value="Pérdida/Robo" {{ old('reason') == 'Pérdida/Robo' ? 'selected' : '' }}>Pérdida/Robo</option>
                    <option value="Otro" {{ old('reason') == 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
                
                <label for="fecha-consumo">Fecha de Consumo:</label>
                <input type="date" id="fecha-consumo" name="consumption_date" value="{{ old('consumption_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                
                <label for="notas">Notas (Opcional):</label>
                <textarea id="notas" name="notes" rows="3" placeholder="Detalles adicionales...">{{ old('notes') }}</textarea>
                
                <button type="submit" class="btn-listo" style="margin-top: 15px;">Registrar Consumo</button>
            </form>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>📉 Historial de Consumos</h2>
        </div>

        @if(session('success'))
            <div style="background-color: #d4edda; color: #155724; padding: 12px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #28a745;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background-color: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                <strong>Errores de validación:</strong>
                <ul style="margin: 5px 0 0 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Motivo</th>
                    <th>Notas</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($consumptions as $consumption)
                    <tr>
                        <td>{{ $consumption->consumption_date->format('d/m/Y') }}</td>
                        <td>{{ $consumption->product->name }}</td>
                        <td><strong>{{ $consumption->quantity }}</strong></td>
                        <td>
                            <span style="padding: 3px 8px; border-radius: 3px; font-size: 0.85em; 
                                @if($consumption->reason == 'Producto dañado' || $consumption->reason == 'Producto vencido' || $consumption->reason == 'Pérdida/Robo')
                                    background-color: #f8d7da; color: #721c24;
                                @elseif($consumption->reason == 'Uso interno')
                                    background-color: #d1ecf1; color: #0c5460;
                                @else
                                    background-color: #fff3cd; color: #856404;
                                @endif
                            ">
                                {{ $consumption->reason }}
                            </span>
                        </td>
                        <td>{{ $consumption->notes ?? '-' }}</td>
                        <td>{{ $consumption->user->name }}</td>
                        <td class="acciones">
                            <form action="{{ route('consumption.destroy', $consumption->id) }}" method="POST" style="display: inline-block;" data-item-name="Consumo de {{ $consumption->product->name }}" data-item-type="consumo" data-warning-message="Esto restaurará el stock del producto">
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
// Mostrar stock disponible al seleccionar producto
document.getElementById('producto').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const stock = selectedOption.getAttribute('data-stock');
    const stockDisplay = document.getElementById('stock-disponible');
    
    if (stock) {
        stockDisplay.textContent = `Stock disponible: ${stock} unidades`;
        stockDisplay.style.color = stock > 10 ? '#28a745' : '#dc3545';
    } else {
        stockDisplay.textContent = '';
    }
});

// Validar cantidad antes de enviar
document.getElementById('form-consumo').addEventListener('submit', function(e) {
    const productoSelect = document.getElementById('producto');
    const selectedOption = productoSelect.options[productoSelect.selectedIndex];
    const stock = parseFloat(selectedOption.getAttribute('data-stock'));
    const cantidad = parseFloat(document.getElementById('cantidad').value);
    
    if (cantidad > stock) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Stock insuficiente',
            text: `Solo hay ${stock} unidades disponibles`,
            confirmButtonColor: '#dc3545'
        });
    }
});
</script>
@endpush
@endsection
