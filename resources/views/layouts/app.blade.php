<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Sistema de Administración</title>
    <link rel="stylesheet" href="{{ asset('css/admin-system.css') }}">
    @stack('styles')
</head>
<body>
    <div class="contenedor-principal">
        
        <header class="barra-superior">
            <nav>
                <a href="{{ route('inventory.index') }}">Administracion</a>
                <a href="{{ route('inventory.index') }}">Inventario</a>
                <a href="{{ route('sales.index') }}">Ventas</a>
                <a href="{{ route('services.index') }}">Servicios</a>
                <a href="{{ route('cash.index') }}">Estado de caja</a>
                <a href="{{ route('invoices.index') }}">Facturas</a>
                
                @auth
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: #333; cursor: pointer; font-weight: bold;">
                            Cerrar Sesión
                        </button>
                    </form>
                @endauth
            </nav>
        </header>

        <div class="contenido-flex">
            @yield('sidebar')
            
            <main class="seccion-inventario">
                @yield('content')
            </main>
        </div>
    </div>
    
    <script src="{{ asset('js/admin-system.js') }}"></script>
    @stack('scripts')
</body>
</html>