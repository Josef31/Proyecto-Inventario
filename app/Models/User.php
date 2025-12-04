<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con el rol del usuario
     */
    public function role()
    {
        return $this->belongsTo(UserRole::class, 'id_role');
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin()
    {
        return $this->id_role === 1;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($roleId)
    {
        return $this->id_role === $roleId;
    }

    /**
     * Verificar si el usuario puede acceder a un módulo
     */
    public function canAccess($module)
    {
        $roleId = $this->id_role;

        // Administrador puede acceder a todo
        if ($roleId === 1) {
            return true;
        }

        // Gerente puede acceder a todo excepto usuarios
        if ($roleId === 2) {
            return $module !== 'users';
        }

        // Cajero solo puede acceder a ventas y compras (NO dashboard)
        if ($roleId === 3) {
            return in_array($module, ['sales', 'purchases']);
        }

        return false;
    }
}