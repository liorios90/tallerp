<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkshopController;

Route::get('/', [WorkshopController::class, 'dashboard'])->name('dashboard');
Route::get('/recepcion', [WorkshopController::class, 'reception'])->name('reception');
Route::post('/ordenes', [WorkshopController::class, 'store'])->name('orders.store');
Route::get('/ordenes', [WorkshopController::class, 'orders'])->name('orders.index');
Route::get('/ordenes/{id}', [WorkshopController::class, 'showOrder'])->whereNumber('id')->name('orders.show');
Route::patch('/ordenes/{id}', [WorkshopController::class, 'updateOrder'])->whereNumber('id')->name('orders.update');
Route::post('/ordenes/{id}/repuestos', [WorkshopController::class, 'addOrderItem'])->whereNumber('id')->name('orders.items.store');
Route::patch('/ordenes/{id}/repuestos/{lineId}', [WorkshopController::class, 'updateOrderItem'])->whereNumber('id')->whereNumber('lineId')->name('orders.items.update');
Route::delete('/ordenes/{id}/repuestos/{lineId}', [WorkshopController::class, 'removeOrderItem'])->whereNumber('id')->whereNumber('lineId')->name('orders.items.destroy');
Route::post('/ordenes/{id}/servicios', [WorkshopController::class, 'storeOrderService'])->whereNumber('id')->name('orders.services.store');
Route::patch('/ordenes/{id}/servicios/{serviceId}', [WorkshopController::class, 'updateOrderService'])->whereNumber('id')->whereNumber('serviceId')->name('orders.services.update');
Route::delete('/ordenes/{id}/servicios/{serviceId}', [WorkshopController::class, 'destroyOrderService'])->whereNumber('id')->whereNumber('serviceId')->name('orders.services.destroy');
Route::get('/seguimiento/{id}', [WorkshopController::class, 'publicOrderStatus'])->whereNumber('id')->middleware('signed')->name('orders.public-status');
Route::get('/clientes', [WorkshopController::class, 'customers'])->name('customers.index');
Route::get('/inventario', [WorkshopController::class, 'inventory'])->name('inventory.index');
Route::get('/inventario/reporte', [WorkshopController::class, 'inventoryReport'])->name('inventory.report');
Route::post('/inventario/productos', [WorkshopController::class, 'storeInventoryItem'])->name('inventory.items.store');
Route::patch('/inventario/productos/{id}', [WorkshopController::class, 'updateInventoryItem'])->whereNumber('id')->name('inventory.items.update');
Route::get('/compras', [WorkshopController::class, 'purchases'])->name('purchases.index');
Route::get('/compras/nueva', [WorkshopController::class, 'createPurchase'])->name('purchases.create');
Route::post('/compras', [WorkshopController::class, 'storePurchase'])->name('purchases.store');
Route::get('/compras/{id}', [WorkshopController::class, 'showPurchase'])->whereNumber('id')->name('purchases.show');
Route::patch('/compras/{id}', [WorkshopController::class, 'updatePurchase'])->whereNumber('id')->name('purchases.update');
Route::post('/compras/{id}/finalizar', [WorkshopController::class, 'finalizePurchase'])->whereNumber('id')->name('purchases.finalize');
Route::delete('/compras/{id}', [WorkshopController::class, 'destroyPurchase'])->whereNumber('id')->name('purchases.destroy');
Route::get('/facturacion', [WorkshopController::class, 'billing'])->name('billing.index');
Route::get('/facturacion/nueva', [WorkshopController::class, 'createInvoice'])->name('billing.create');
Route::post('/facturacion', [WorkshopController::class, 'storeInvoice'])->name('billing.store');
Route::get('/facturacion/{id}', [WorkshopController::class, 'showInvoice'])->whereNumber('id')->name('billing.show');
Route::patch('/facturacion/{id}', [WorkshopController::class, 'updateInvoice'])->whereNumber('id')->name('billing.update');
Route::post('/facturacion/{id}/finalizar', [WorkshopController::class, 'finalizeInvoice'])->whereNumber('id')->name('billing.finalize');
Route::delete('/facturacion/{id}', [WorkshopController::class, 'destroyInvoice'])->whereNumber('id')->name('billing.destroy');
