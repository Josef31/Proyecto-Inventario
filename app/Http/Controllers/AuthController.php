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
            return redirect()->route('inventory.index');
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