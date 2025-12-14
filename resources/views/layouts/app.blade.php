<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | Sistema de Administración</title>
    <link rel="stylesheet" href="{{ asset('css/admin-system.css') }}">
    @stack('pre-vite-scripts')
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body>
    
    <header class="barra-superior">
        <nav>
            <div class="nav-links">
                {{-- Dashboard - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('dashboard'))
                    <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                        Administración
                    </a>
                @endif
                
                {{-- Ventas - Cajero, Gerente, Admin --}}
                @if(auth()->user()->canAccess('sales'))
                    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">
                        Ventas
                    </a>
                @endif
                
                {{-- Compras - Cajero, Gerente, Admin --}}
                @if(auth()->user()->canAccess('purchases'))
                    <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                        Compras
                    </a>
                @endif
                
                {{-- Inventario - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('inventory'))
                    <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        Inventario
                    </a>
                @endif
                
                {{-- Clientes - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('customers'))
                    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        Clientes
                    </a>
                @endif
                
                {{-- Proveedores - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('suppliers'))
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        Proveedores
                    </a>
                @endif
                
                {{-- Servicios - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('services'))
                    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'active' : '' }}">
                        Servicios
                    </a>
                @endif
                
                {{-- Caja - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('cash'))
                    <a href="{{ route('cash.index') }}" class="{{ request()->routeIs('cash.*') ? 'active' : '' }}">
                        Caja
                    </a>
                @endif
                
                {{-- Facturas - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('invoices'))
                    <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        Facturas
                    </a>
                @endif
                
                {{-- Consumos - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('inventory'))
                    <a href="{{ route('consumption.index') }}" class="{{ request()->routeIs('consumption.*') ? 'active' : '' }}">
                        Consumos
                    </a>
                @endif
                
                {{-- Tasas de Cambio - Solo Gerente y Admin --}}
                @if(auth()->user()->canAccess('exchange_rates'))
                    <a href="{{ route('exchange_rates.index') }}" class="{{ request()->routeIs('exchange_rates.*') ? 'active' : '' }}">
                        Tasas
                    </a>
                @endif
                
                {{-- Usuarios - Solo Admin --}}
                @if(auth()->user()->canAccess('users'))
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        Usuarios
                    </a>
                @endif
            </div>
            
            {{-- Notification Bell --}}
            @php
                // Productos con stock bajo: stock <= stock_minimum * 5
                $lowStockProducts = \App\Models\Product::whereRaw('stock_initial <= stock_minimum * 5')
                    ->orderBy('stock_initial', 'asc')
                    ->get();
                $notificationCount = $lowStockProducts->count();
            @endphp
            
            <div class="notification-bell" style="position: relative; margin-right: 20px;">
                <button data-bs-toggle="modal" data-bs-target="#notificationModal" style="background: none; border: none; cursor: pointer; position: relative; padding: 8px; font-size: 24px;">
                    🔔
                    @if($notificationCount > 0)
                        <span class="notification-badge" style="position: absolute; top: 0; right: 0; background-color: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                        </span>
                    @endif
                </button>
            </div>
            
            {{-- User Info & Logout --}}
            @auth
                <div class="user-info">
                    <div class="user-details">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-role">{{ auth()->user()->role->name ?? 'Usuario' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            @endauth
        </nav>
    </header>
    <div class="contenedor-principal">
        

        <div class="contenido-flex">
            @yield('sidebar')
            
            <main class="seccion-inventario">
                @yield('content')
            </main>
        </div>
    </div>
    
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Toastify({
                text: "✅ {{ session('success') }}",
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: "#27ae60"
            }).showToast();
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                confirmButtonColor: '#e74c3c'
            });
        });
    </script>
    @endif
    
    <script src="{{ asset('js/admin-system.js') }}"></script>
    <script src="{{ asset('js/delete-confirmation.js') }}"></script>
    
    {{-- Notification Bell JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBtn = document.getElementById('notification-btn');
            const notificationDropdown = document.getElementById('notification-dropdown');
            
            if (notificationBtn && notificationDropdown) {
                // Toggle dropdown on button click
                notificationBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isVisible = notificationDropdown.style.display === 'block';
                    notificationDropdown.style.display = isVisible ? 'none' : 'block';
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.style.display = 'none';
    <!-- Modal de Notificaciones -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                    <h5 class="modal-title" id="notificationModalLabel" style="color: #333;">
                        ⚠️ Productos con Stock Bajo
                        @if(isset($notificationCount) && $notificationCount > 0)
                            <span class="badge bg-danger ms-2">{{ $notificationCount }}</span>
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Producto</th>
                                        <th class="text-center">Stock Actual</th>
                                        <th class="text-center">Stock Mínimo</th>
                                        <th class="text-end pe-4">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockProducts as $product)
                                        <tr>
                                            <td class="ps-4 align-middle">
                                                <strong>{{ $product->name }}</strong>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge {{ $product->stock_initial == 0 ? 'bg-danger' : 'bg-warning text-dark' }}" style="font-size: 0.9em;">
                                                    {{ $product->stock_initial }}
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $product->stock_minimum }}
                                            </td>
                                            <td class="text-end pe-4 align-middle">
                                                <a href="{{ route('inventory.index', ['search' => $product->name]) }}" class="btn btn-sm btn-primary">
                                                    Ver en Inventario
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div style="font-size: 48px; margin-bottom: 15px;">✅</div>
                            <h5 class="text-muted">¡Todo está en orden!</h5>
                            <p class="text-muted mb-0">No hay productos con stock bajo en este momento.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer" style="background-color: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-primary">Ir al Inventario Completo</a>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>