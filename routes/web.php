<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Ruta principal de inventario
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    
    // Rutas para AJAX del inventario
    Route::post('/inventory/store', [InventoryController::class, 'store']);
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy']);
    
    // Rutas temporales para las otras páginas
    Route::get('/sales', function () {
        return 'Página de Ventas - En construcción';
    });
    
    Route::get('/services', function () {
        return 'Página de Servicios - En construcción';
    });
    
    Route::get('/cash', function () {
        return 'Página de Caja - En construcción';
    });
    
    Route::get('/invoices', function () {
        return 'Página de Facturas - En construcción';
    });
});