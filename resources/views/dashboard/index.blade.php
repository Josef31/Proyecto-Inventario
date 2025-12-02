@extends('layouts.app')

@section('title', 'Dashboard | Sistema de Administración')

@section('content')
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
            <div class="subvalue">Total en stock</div>
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
                    <td>${{ number_format($product->price, 2) }}</td>
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
            <h2>🛒 Ventas Recientes</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer_name ?? 'N/A' }}</td>
                        <td>${{ number_format($sale->subtotal + $sale->taxes, 2) }}</td>
                        <td>{{ $sale->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No hay ventas recientes</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Recent Purchases -->
        <div class="dashboard-section">
            <h2>📦 Compras Recientes</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPurchases as $purchase)
                    <tr>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                        <td>${{ number_format($purchase->total, 2) }}</td>
                        <td>{{ $purchase->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No hay compras recientes</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
