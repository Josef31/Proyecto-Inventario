<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validar los datos - usando 'name' en lugar de 'email'
        $credentials = $request->validate([
            'username' => 'required|string', // Cambiamos a username
            'password' => 'required',
        ]);

        // Intentar autenticar usando el campo 'name'
        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            
            // Redirigir según el rol del usuario
            $user = Auth::user();
            
            // Cajero (ID: 3) va directo a ventas
            if ($user->id_role === 3) {
                return redirect()->intended(route('sales.index'));
            }
            
            // Gerente y Admin van al dashboard
            return redirect()->intended(route('dashboard.index'));
        }

        // Si falla la autenticación
        return back()->withErrors([
            'username' => 'Usuario o contraseña incorrectos.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}