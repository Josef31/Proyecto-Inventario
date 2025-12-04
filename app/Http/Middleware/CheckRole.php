<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRoleId = $user->id_role;

        // Convert role names to IDs
        $roleMap = [
            'admin' => 1,
            'gerente' => 2,
            'cajero' => 3,
        ];

        $allowedRoleIds = [];
        foreach ($roles as $role) {
            if (isset($roleMap[$role])) {
                $allowedRoleIds[] = $roleMap[$role];
            }
        }

        if (in_array($userRoleId, $allowedRoleIds)) {
            return $next($request);
        }

        // Unauthorized access
        abort(403, 'No tienes permiso para acceder a este módulo.');
    }
}
