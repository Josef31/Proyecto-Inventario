<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | Sistema de Administración</title>
    <link rel="stylesheet" href="{{ asset('css/admin-system.css') }}">
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
    @stack('scripts')
</body>
</html>