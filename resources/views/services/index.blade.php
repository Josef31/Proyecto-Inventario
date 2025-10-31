@extends('layouts.app')

@section('title', 'Servicios | Sistema de Administración')

@section('content')
<div class="contenido-flex">
    
    <aside class="barra-lateral">
        <div class="perfil">
            <div class="icono-perfil"></div>
            <p class="nombre-usuario">{{ auth()->user()->name }}</p>
            <p class="rol-usuario">Administrador</p>
        </div>

        <div class="seccion-formulario">
            <h3>NUEVO SERVICIO</h3>
            
            <label for="nombre-servicio">Nombre del Servicio:</label>
            <input type="text" id="nombre-servicio" placeholder="Ej: Cambio de aceite">
            
            <label for="descripcion-servicio">Descripción:</label>
            <textarea id="descripcion-servicio" placeholder="Descripción del servicio..."></textarea>
            
            <label for="costo-base">Costo Base (Materiales/Hora):</label>
            <input type="number" id="costo-base" min="0" step="0.01" placeholder="0.00">
            
            <label for="tarifa-cliente">Tarifa al Cliente:</label>
            <input type="number" id="tarifa-cliente" min="0" step="0.01" placeholder="0.00">

            <label for="duracion-estimada">Duración Estimada (minutos):</label>
            <input type="number" id="duracion-estimada" min="1" placeholder="60">
            
            <button class="btn-listo" onclick="agregarServicio()">Guardar Servicio</button>
        </div>

        <div class="seccion-acciones-admin">
            <h3>REPORTES</h3>
            <button class="btn-admin-accion btn-reporte-servicios" onclick="generarReporte()">Generar Reporte de Servicios</button>
        </div>
    </aside>

    <main class="seccion-inventario">
        <div class="cabecera-inventario">
            <h2>Servicios Disponibles</h2>
            <p class="total-invertido">Total de Servicios: <span id="total-servicios">{{ $totalServices }}</span></p>
        </div>

        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre del Servicio</th>
                    <th>Descripción</th>
                    <th>Costo Base</th>
                    <th>Tarifa Cliente</th>
                    <th>Duración (min)</th>
                    <th>Ganancia Estimada</th>
                    <th>Margen %</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo-servicios">
                @foreach($services as $index => $service)
                <tr id="servicio-{{ $service->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $service->name }}</strong></td>
                    <td>{{ $service->description ?? 'Sin descripción' }}</td>
                    <td>${{ number_format($service->base_cost, 2) }}</td>
                    <td>${{ number_format($service->customer_rate, 2) }}</td>
                    <td>{{ $service->estimated_duration }}</td>
                    <td>${{ number_format($service->estimated_profit, 2) }}</td>
                    <td>{{ number_format($service->profit_margin, 1) }}%</td>
                    <td>
                        <button class="btn-editar" onclick="editarServicio({{ $service->id }})">Editar</button>
                        <button class="btn-eliminar" onclick="eliminarServicio({{ $service->id }})">Eliminar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>

<!-- Modal para editar servicio -->
<div id="modal-editar" class="modal" style="display: none;">
    <div class="modal-contenido">
        <h3>Editar Servicio</h3>
        <form id="form-editar-servicio">
            <input type="hidden" id="editar-id">
            
            <label for="editar-nombre">Nombre del Servicio:</label>
            <input type="text" id="editar-nombre" required>
            
            <label for="editar-descripcion">Descripción:</label>
            <textarea id="editar-descripcion"></textarea>
            
            <label for="editar-costo-base">Costo Base:</label>
            <input type="number" id="editar-costo-base" min="0" step="0.01" required>
            
            <label for="editar-tarifa-cliente">Tarifa al Cliente:</label>
            <input type="number" id="editar-tarifa-cliente" min="0" step="0.01" required>

            <label for="editar-duracion">Duración Estimada (minutos):</label>
            <input type="number" id="editar-duracion" min="1" required>
            
            <div class="modal-botones">
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-listo">Actualizar Servicio</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let servicioEditando = null;

function agregarServicio() {
    const servicio = {
        name: document.getElementById('nombre-servicio').value,
        description: document.getElementById('descripcion-servicio').value,
        base_cost: parseFloat(document.getElementById('costo-base').value),
        customer_rate: parseFloat(document.getElementById('tarifa-cliente').value),
        estimated_duration: parseInt(document.getElementById('duracion-estimada').value)
    };

    // Validaciones
    if (!servicio.name || !servicio.base_cost || !servicio.customer_rate || !servicio.estimated_duration) {
        alert('Por favor complete todos los campos requeridos');
        return;
    }

    if (servicio.customer_rate <= servicio.base_cost) {
        alert('La tarifa al cliente debe ser mayor al costo base');
        return;
    }

    fetch('/services/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(servicio)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Servicio agregado exitosamente');
            limpiarFormulario();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el servicio');
    });
}

function editarServicio(id) {
    fetch('/services/get')
    .then(response => response.json())
    .then(services => {
        const servicio = services.find(s => s.id === id);
        if (servicio) {
            servicioEditando = servicio;
            
            document.getElementById('editar-id').value = servicio.id;
            document.getElementById('editar-nombre').value = servicio.name;
            document.getElementById('editar-descripcion').value = servicio.description || '';
            document.getElementById('editar-costo-base').value = servicio.base_cost;
            document.getElementById('editar-tarifa-cliente').value = servicio.customer_rate;
            document.getElementById('editar-duracion').value = servicio.estimated_duration;
            
            document.getElementById('modal-editar').style.display = 'block';
        }
    });
}

function eliminarServicio(id) {
    if (!confirm('¿Está seguro de que desea eliminar este servicio?')) {
        return;
    }

    fetch(`/services/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Servicio eliminado exitosamente');
            document.getElementById(`servicio-${id}`).remove();
            actualizarContador();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el servicio');
    });
}

function cerrarModal() {
    document.getElementById('modal-editar').style.display = 'none';
    servicioEditando = null;
}

function limpiarFormulario() {
    document.getElementById('nombre-servicio').value = '';
    document.getElementById('descripcion-servicio').value = '';
    document.getElementById('costo-base').value = '';
    document.getElementById('tarifa-cliente').value = '';
    document.getElementById('duracion-estimada').value = '';
}

function actualizarContador() {
    const total = document.querySelectorAll('#tabla-cuerpo-servicios tr').length;
    document.getElementById('total-servicios').textContent = total;
}

function generarReporte() {
    alert('Generando reporte de servicios...');
}

// Event listener para el formulario de edición
document.getElementById('form-editar-servicio').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const servicio = {
        name: document.getElementById('editar-nombre').value,
        description: document.getElementById('editar-descripcion').value,
        base_cost: parseFloat(document.getElementById('editar-costo-base').value),
        customer_rate: parseFloat(document.getElementById('editar-tarifa-cliente').value),
        estimated_duration: parseInt(document.getElementById('editar-duracion').value)
    };

    if (servicio.customer_rate <= servicio.base_cost) {
        alert('La tarifa al cliente debe ser mayor al costo base');
        return;
    }

    fetch(`/services/${servicioEditando.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(servicio)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Servicio actualizado exitosamente');
            cerrarModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el servicio');
    });
});
</script>
@endsection