<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema de Administración</title>
    <style>
        /* Estilos específicos para centrar el formulario de login */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #34495e;
            margin: 0;
            font-family: sans-serif;
        }
        .login-contenedor {
            width: 350px;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .login-contenedor h2 {
            color: #34495e;
            margin-bottom: 25px;
            font-size: 1.8em;
        }
        .login-contenedor input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #27ae60;
        }
        .alerta-error {
            color: #e74c3c;
            margin-top: 15px;
            font-weight: bold;
            padding: 10px;
            background-color: #fcebeb;
            border-radius: 4px;
            display: none; /* Oculto por defecto */
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.9em;
            display: block;
            margin-bottom: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-contenedor">
        <h2>ADMINISTRACIÓN - LOGIN</h2>
        
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            
            <input type="text" id="username" name="username" placeholder="Usuario" required value="{{ old('username') }}">
            @error('username')
                <span class="error-message">{{ $message }}</span>
            @enderror
            
            <input type="password" id="password" name="password" placeholder="Contraseña" required>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
            
            <button type="submit" class="btn-login">Ingresar</button>
            
            @if ($errors->any())
                <div class="alerta-error" id="login-error" style="display: block;">
                    {{ $errors->first() }}
                </div>
            @endif
        </form>
    </div>

    <script>
        // Debug: Verificar que el formulario se envíe
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                console.log('Formulario enviado');
                console.log('Usuario:', document.getElementById('username').value);
                console.log('Contraseña:', document.getElementById('password').value);
            });

            // Mostrar/ocultar alerta de error
            const errorDiv = document.getElementById('login-error');
            if (errorDiv) {
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>