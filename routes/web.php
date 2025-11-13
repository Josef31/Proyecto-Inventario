<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\InvoicesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Rutas de Inventario
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/inventory/adjustment', [InventoryController::class, 'showAdjustmentForm'])->name('inventory.adjustment.form');

    // Rutas para ventas
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/search', [SalesController::class, 'searchProducts'])->name('sales.search');
    Route::post('/sales/process', [SalesController::class, 'processSale'])->name('sales.process');
    Route::get('/sales/today', [SalesController::class, 'getTodaySales'])->name('sales.today');

    // Rutas para servicios - AGREGAR LA RUTA FALTANTE
    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
    Route::post('/services/store', [ServicesController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit', [ServicesController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}', [ServicesController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServicesController::class, 'destroy'])->name('services.destroy');
    Route::get('/services/get', [ServicesController::class, 'getServices'])->name('services.get');

    // Rutas para caja
    Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
    Route::post('/cash/open', [CashController::class, 'openCashRegister'])->name('cash.open');
    Route::post('/cash/close', [CashController::class, 'closeCashRegister'])->name('cash.close');
    Route::get('/cash/registers', [CashController::class, 'getCashRegisters'])->name('cash.registers');
    Route::get('/cash/today-sales', [CashController::class, 'getTodayCashSales'])->name('cash.today-sales');

    // Rutas para facturas
    Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [InvoicesController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{id}/print', [InvoicesController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{id}/details', [InvoicesController::class, 'getInvoiceDetails'])->name('invoices.details');
    Route::get('/invoices/get/all', [InvoicesController::class, 'getInvoices'])->name('invoices.get');
});