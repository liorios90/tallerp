<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkshopController;

Route::get('/', [WorkshopController::class, 'dashboard'])->name('dashboard');
Route::get('/recepcion', [WorkshopController::class, 'reception'])->name('reception');
Route::post('/ordenes', [WorkshopController::class, 'store'])->name('orders.store');
Route::get('/ordenes', [WorkshopController::class, 'orders'])->name('orders.index');
Route::get('/ordenes/{id}', [WorkshopController::class, 'showOrder'])->whereNumber('id')->name('orders.show');
Route::patch('/ordenes/{id}', [WorkshopController::class, 'updateOrder'])->whereNumber('id')->name('orders.update');
Route::get('/seguimiento/{id}', [WorkshopController::class, 'publicOrderStatus'])->whereNumber('id')->middleware('signed')->name('orders.public-status');
Route::get('/clientes', [WorkshopController::class, 'customers'])->name('customers.index');
Route::get('/inventario', [WorkshopController::class, 'inventory'])->name('inventory.index');
Route::get('/facturacion', [WorkshopController::class, 'billing'])->name('billing.index');
