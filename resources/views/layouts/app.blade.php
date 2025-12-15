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
                <button id="notification-btn" style="background: none; border: none; cursor: pointer; position: relative; padding: 8px; font-size: 24px;">
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
    
    {{-- Notification Modal JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBtn = document.getElementById('notification-btn');
            const notificationModal = document.getElementById('notificationModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const modalOverlay = document.getElementById('modalOverlay');
            
            if (notificationBtn && notificationModal) {
                // Open modal on button click
                notificationBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden'; // Prevent background scrolling
                });
                
                // Close modal when clicking the close button
                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', function() {
                        notificationModal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    });
                }
                
                // Close modal when clicking the overlay (outside the modal content)
                if (modalOverlay) {
                    modalOverlay.addEventListener('click', function(e) {
                        if (e.target === modalOverlay) {
                            notificationModal.style.display = 'none';
                            document.body.style.overflow = 'auto';
                        }
                    });
                }
                
                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && notificationModal.style.display === 'flex') {
                        notificationModal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });
    </script>
    
    <!-- Modal de Notificaciones -->
    <div id="notificationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div id="modalOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>
        <div style="position: relative; background: white; border-radius: 8px; max-width: 900px; width: 90%; max-height: 80vh; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); z-index: 10000;">
            <!-- Modal Header -->
            <div style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h5 style="color: #333; margin: 0; font-size: 1.25rem; font-weight: 600;">
                    ⚠️ Productos con Stock Bajo
                    @if(isset($notificationCount) && $notificationCount > 0)
                        <span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.875rem; margin-left: 10px;">{{ $notificationCount }}</span>
                    @endif
                </h5>
                <button id="closeModalBtn" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; line-height: 1; padding: 0; width: 30px; height: 30px;" aria-label="Close">&times;</button>
            </div>
            
            <!-- Modal Body -->
            <div style="overflow-y: auto; max-height: calc(80vh - 140px);">
                @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th style="padding: 12px 20px; text-align: left; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Producto</th>
                                <th style="padding: 12px 20px; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Stock Actual</th>
                                <th style="padding: 12px 20px; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Stock Mínimo</th>
                                <th style="padding: 12px 20px; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                                <tr style="border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                    <td style="padding: 15px 20px;">
                                        <strong style="color: #333;">{{ $product->name }}</strong>
                                    </td>
                                    <td style="padding: 15px 20px; text-align: center;">
                                        <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 0.875rem; font-weight: 600; {{ $product->stock_initial == 0 ? 'background-color: #dc3545; color: white;' : 'background-color: #ffc107; color: #333;' }}">
                                            {{ $product->stock_initial }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px 20px; text-align: center; color: #666;">
                                        {{ $product->stock_minimum }}
                                    </td>
                                    <td style="padding: 15px 20px; text-align: right;">
                                        <a href="{{ route('inventory.index', ['search' => $product->name]) }}" style="display: inline-block; padding: 6px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 0.875rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#0056b3'" onmouseout="this.style.backgroundColor='#007bff'">
                                            Ver en Inventario
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 48px; margin-bottom: 15px;">✅</div>
                        <h5 style="color: #6c757d; font-weight: 500; margin-bottom: 8px;">¡Todo está en orden!</h5>
                        <p style="color: #6c757d; margin: 0;">No hay productos con stock bajo en este momento.</p>
                    </div>
                @endif
            </div>
            
            <!-- Modal Footer -->
            <div style="background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="document.getElementById('notificationModal').style.display='none'; document.body.style.overflow='auto';" style="padding: 8px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#5a6268'" onmouseout="this.style.backgroundColor='#6c757d'">
                    Cerrar
                </button>
                <a href="{{ route('inventory.index') }}" style="padding: 8px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 0.875rem; transition: background-color 0.2s; display: inline-block;" onmouseover="this.style.backgroundColor='#0056b3'" onmouseout="this.style.backgroundColor='#007bff'">
                    Ir al Inventario Completo
                </a>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>