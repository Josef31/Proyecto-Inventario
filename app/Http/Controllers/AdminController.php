<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $userData = [
        'name' => 'luishck',
        'role' => 'Administrador'
    ];

    protected function getSidebarData()
    {
        return $this->userData;
    }
}