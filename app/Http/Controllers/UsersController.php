<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('id')->get();
        $roles = UserRole::all();
        
        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'id_role' => 'required|exists:users_role,id',
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'id_role' => $request->id_role,
            ]);

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::with('role')->findOrFail($id);
        $roles = UserRole::all();
        
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'id_role' => 'required|exists:users_role,id',
        ]);

        try {
            // Prevenir cambio de rol del admin
            if ($user->isAdmin() && $request->id_role != $user->id_role) {
                return redirect()->route('users.edit', $id)
                    ->with('error', 'No se puede cambiar el rol del usuario administrador');
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'id_role' => $request->id_role,
            ];

            // Solo actualizar password si se proporciona uno nuevo
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('users.edit', $id)
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevenir eliminación del admin
            if ($user->isAdmin()) {
                return redirect()->route('users.index')
                    ->with('error', 'No se puede eliminar el usuario administrador');
            }

            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }
}
