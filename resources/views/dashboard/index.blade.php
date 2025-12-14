@extends('layouts.app')

@section('title', 'Dashboard | Sistema de Administración')

@push('pre-vite-scripts')
<!-- jQuery loaded BEFORE Vite to prevent conflicts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@endpush

@push('styles')
<!-- DataTables CSS from CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<!-- FontAwesome for Buttons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endpush

@section('content')
<!-- Dashboard v2.1 - DataTables Enhanced - 2025-12-05 11:51 -->
<style>
    .dashboard-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-header {
        margin-bottom: 30px;
    }

    .dashboard-header h1 {
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .dashboard-header p {
        color: #7f8c8d;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .metric-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #3498db;
    }

    .metric-card.success {
        border-left-color: #2ecc71;
    }

    .metric-card.warning {
        border-left-color: #f39c12;
    }

    .metric-card.danger {
        border-left-color: #e74c3c;
    }

    .metric-card h3 {
        font-size: 0.9em;
        color: #7f8c8d;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .metric-card .value {
        font-size: 2em;
        font-weight: bold;
        color: #2c3e50;
    }

    .metric-card .subvalue {
        font-size: 0.85em;
        color: #95a5a6;
        margin-top: 5px;
    }

    .dashboard-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .dashboard-section h2 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 1.3em;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 10px;
    }

    .two-column-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 20px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #ecf0f1;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
    }

    .data-table td {
        padding: 10px;
        border-bottom: 1px solid #ecf0f1;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }

    .badge-danger {
        background: #f8d7da;
        color: #721c24;
    }

    .alert-box {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .alert-warning {
        background: #fff3cd;
        border-left: 4px solid #f39c12;
        color: #856404;
    }

    .alert-danger {
        background: #f8d7da;
        border-left: 4px solid #e74c3c;
        color: #721c24;
    }

    /* DataTables Styles - Bootstrap 5 Enhanced */
    .dataTables_wrapper {
        padding: 20px 0;
    }

    /* Buttons Container */
    .dt-buttons {
        display: inline-flex;
        gap: 5px;
        margin-left: 10px;
        vertical-align: middle;
    }

    /* Ensure buttons look like Bootstrap buttons even if DataTables adds its own classes */
    .dt-button {
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
        box-shadow: none;
    }

    .dt-button.btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        color: white !important;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        transition: all 0.2s ease !important;
    }

    .dt-button.btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.15) !important;
    }

    /* Specific colors to ensure they stick */
    .dt-button.btn-secondary { background-color: #6c757d !important; }
    .dt-button.btn-success { background-color: #198754 !important; }
    .dt-button.btn-danger { background-color: #dc3545 !important; }
    .dt-button.btn-info { 
        background-color: #0dcaf0 !important; 
        color: #000 !important; /* Info usually has black text */
    }

    /* Search and Length Controls - Reset floats for Flexbox */
    .dataTables_wrapper .dataTables_filter {
        text-align: right;
        margin-bottom: 0;
    }

    .dataTables_wrapper .dataTables_filter label {
        font-weight: 500;
        color: #495057;
        display: inline-flex;
        align-items: center;
    }

    .dataTables_wrapper .dataTables_filter input {
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        margin-left: 0.5rem;
        font-size: 0.875rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .dataTables_wrapper .dataTables_length {
        margin-bottom: 0;
        margin-right: 10px;
    }

    .dataTables_wrapper .dataTables_length label {
        font-weight: 500;
        color: #495057;
        display: inline-flex;
        align-items: center;
    }

    .dataTables_wrapper .dataTables_length select {
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        margin: 0 0.5rem;
        font-size: 0.875rem;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        appearance: none;
    }

    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Pagination */
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
        padding-top: 15px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-block;
        padding: 0.375rem 0.75rem !important;
        margin: 0 2px !important;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.5;
        color: #0d6efd !important;
        text-decoration: none;
        background-color: #fff !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.25rem !important;
        transition: all 0.15s ease-in-out;
        cursor: pointer !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: #0a58ca !important;
        background-color: #e9ecef !important;
        border-color: #dee2e6 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        color: #fff !important;
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #fff !important;
        background-color: #0b5ed7 !important;
        border-color: #0a58ca !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #6c757d !important;
        pointer-events: none;
        background-color: #fff !important;
        border-color: #dee2e6 !important;
        opacity: 0.5;
    }

    /* Info Text */
    .dataTables_wrapper .dataTables_info {
        clear: both;
        float: left;
        padding-top: 15px;
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 400;
    }

    /* Table Styling */
    .dataTables_wrapper table.dataTable {
        clear: both;
        margin-top: 10px !important;
        margin-bottom: 10px !important;
        max-width: none !important;
        border-collapse: separate !important;
    }

    .dataTables_wrapper table.dataTable thead th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>📊 Dashboard de Administración</h1>
        <p>Resumen general del negocio - {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Metrics Cards -->
    <div class="metrics-grid">
        <div class="metric-card success">
            <h3>Ingresos Hoy (Bs)</h3>
            <div class="value">{{ number_format($todayRevenueBs, 2) }}</div>
            <div class="subvalue">Bolívares</div>
        </div>

        <div class="metric-card success">
            <h3>Ingresos Hoy (USD)</h3>
            <div class="value">${{ number_format($todayRevenueUsd, 2) }}</div>
            <div class="subvalue">Dólares</div>
        </div>

        <div class="metric-card">
            <h3>Ventas Esta Semana</h3>
            <div class="value">{{ $weekSales }}</div>
            <div class="subvalue">Transacciones</div>
        </div>

        <div class="metric-card">
            <h3>Ventas Este Mes</h3>
            <div class="value">{{ $monthSales }}</div>
            <div class="subvalue">Transacciones</div>
        </div>

        <div class="metric-card warning">
            <h3>Valor Inventario</h3>
            <div class="value">${{ number_format($inventoryValue, 2) }}</div>
            <div class="subvalue">Precio de venta</div>
        </div>

        <div class="metric-card">
            <h3>Costo Inventario</h3>
            <div class="value">${{ number_format($inventoryCost, 2) }}</div>
            <div class="subvalue">Precio de compra</div>
        </div>

        <div class="metric-card success">
            <h3>Ganancia Potencial</h3>
            <div class="value">${{ number_format($inventoryProfit, 2) }}</div>
            <div class="subvalue">Sin impuestos/intereses</div>
        </div>

        <div class="metric-card {{ $openCashRegister ? 'success' : 'danger' }}">
            <h3>Estado de Caja</h3>
            <div class="value">{{ $openCashRegister ? 'Abierta' : 'Cerrada' }}</div>
            <div class="subvalue">{{ $openCashRegister ? 'Operando' : 'Inactiva' }}</div>
        </div>

        <div class="metric-card">
            <h3>Total Clientes</h3>
            <div class="value">{{ $totalCustomers }}</div>
            <div class="subvalue">{{ $newCustomers }} nuevos este mes</div>
        </div>

        <div class="metric-card {{ $lowStockProducts->count() > 0 ? 'warning' : 'success' }}">
            <h3>Stock Bajo</h3>
            <div class="value">{{ $lowStockProducts->count() }}</div>
            <div class="subvalue">Productos < 10 unidades</div>
        </div>
    </div>

    <!-- Alerts -->
    @if($outOfStockProducts->count() > 0)
    <div class="alert-box alert-danger">
        <strong>⚠️ Alerta:</strong> Hay {{ $outOfStockProducts->count() }} producto(s) sin stock
    </div>
    @endif

    @if($lowStockProducts->count() > 0)
    <div class="alert-box alert-warning">
        <strong>⚠️ Advertencia:</strong> Hay {{ $lowStockProducts->count() }} producto(s) con stock bajo
    </div>
    @endif

    <!-- Two Column Layout -->
    <div class="two-column-grid">
        <!-- Top Selling Products -->
        <div class="dashboard-section">
            <h2>🏆 Productos Más Vendidos</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad Vendida</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topSellingProducts as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td><strong>{{ $product->total_quantity }}</strong> unidades</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" style="text-align: center; color: #999;">No hay datos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Top Revenue Products -->
        <div class="dashboard-section">
            <h2>💰 Productos con Mayor Ingreso</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topRevenueProducts as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td><strong>${{ number_format($product->total_revenue, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" style="text-align: center; color: #999;">No hay datos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="dashboard-section">
        <h2>👥 Mejores Clientes</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Compras</th>
                    <th>Total Gastado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $customer)
                <tr>
                    <td>{{ $customer->customer_name }}</td>
                    <td>{{ $customer->purchase_count }} compras</td>
                    <td><strong>${{ number_format($customer->total_spent, 2) }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #999;">No hay datos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Low Stock Products -->
    @if($lowStockProducts->count() > 0)
    <div class="dashboard-section">
        <h2>⚠️ Productos con Stock Bajo</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Stock Actual</th>
                    <th>Precio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td><strong>{{ $product->stock_initial }}</strong> unidades</td>
                    <td>${{ number_format($product->price_sell, 2) }}</td>
                    <td>
                        @if($product->stock_initial < 5)
                            <span class="badge badge-danger">Crítico</span>
                        @else
                            <span class="badge badge-warning">Bajo</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="two-column-grid">
        <!-- Recent Sales -->
        <div class="dashboard-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0;">🛒 Ventas Recientes</h2>
                <div>
                    <label for="sales-date-filter" style="margin-right: 10px; font-weight: bold;">Fecha:</label>
                    <input type="date" id="sales-date-filter" value="{{ $filterDate }}" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <table class="data-table" id="ventas-table">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer_name ?? 'N/A' }}</td>
                        <td>${{ number_format($sale->total, 2) }}</td>
                        <td>{{ $sale->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Recent Purchases -->
        <div class="dashboard-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0;">📦 Compras Recientes</h2>
                <div>
                    <label for="purchases-date-filter" style="margin-right: 10px; font-weight: bold;">Fecha:</label>
                    <input type="date" id="purchases-date-filter" value="{{ $filterDate }}" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <table class="data-table" id="compras-table">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPurchases as $purchase)
                    <tr>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                        <td>${{ number_format($purchase->total_amount, 2) }}</td>
                        <td>{{ $purchase->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables JS from CDN (jQuery already loaded in head) -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
// Use jQuery in noConflict mode to avoid conflicts with other libraries
jQuery(document).ready(function($) {
    try {
        // Define buttons configuration
        var exportButtons = [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copiar',
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-info btn-sm'
            }
        ];

        // Define language configuration
        var languageConfig = {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            emptyTable: "No hay datos disponibles"
        };

        // DOM Configuration for Bootstrap 5
        // l = length, B = buttons, f = filter, t = table, i = info, p = pagination
        var domConfig = 
            "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center'lB><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";

        // Initialize DataTables for Ventas
        $('#ventas-table').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            dom: domConfig,
            buttons: exportButtons,
            language: $.extend({}, languageConfig, {
                emptyTable: "No hay ventas recientes para esta fecha"
            })
        });

        // Initialize DataTables for Compras
        $('#compras-table').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            dom: domConfig,
            buttons: exportButtons,
            language: $.extend({}, languageConfig, {
                emptyTable: "No hay compras recientes para esta fecha"
            })
        });

        // Date filter handlers
        $('#sales-date-filter').on('change', function() {
            var selectedDate = $(this).val();
            window.location.href = '{{ route("dashboard.index") }}?date=' + selectedDate;
        });

        $('#purchases-date-filter').on('change', function() {
            var selectedDate = $(this).val();
            window.location.href = '{{ route("dashboard.index") }}?date=' + selectedDate;
        });
    } catch (error) {
        console.error('DataTables Initialization Error:', error);
    }
});
</script>
@endsection
