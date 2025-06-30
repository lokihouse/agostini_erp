<?php

use App\Http\Controllers\DashboardProductionPdfController;
use App\Http\Controllers\ProductionOrderPdfController;
use App\Http\Controllers\TimeClockController;
use App\Http\Controllers\VisitWithoutOrderPdfController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::middleware(['auth'])->group(function () {
    Route::get('/production-orders/{uuid}/pdf', [ProductionOrderPdfController::class, 'generatePdf'])
        ->name('production-orders.pdf');

    Route::get('/map-register-point/{actionType}', function (string $actionType) {
        return view('livewire.time-clock.map-register-point', ['actionType' => $actionType]);
    })->name('time-clock.map-register-point');

    Route::post('/time-clock/store', [TimeClockController::class, 'store'])->name('time-clock.store');

    Route::get('/sales-orders/{uuid}/pdf', [\App\Http\Controllers\SalesOrderPdfController::class, 'generatePdf'])
        ->name('sales-orders.pdf');

    Route::get('/transport-orders/{uuid}/pdf', [\App\Http\Controllers\TransportOrderPdfController::class, 'generatePdf'])
        ->name('transport-orders.pdf');

    Route::get('/dp/pdf', [DashboardProductionPdfController::class, 'generatePdf'])
	    ->name('production-dashboard.pdf');

    Route::get('/visits-without-order/pdf', VisitWithoutOrderPdfController::class)->name('visits.without.order.pdf');
});
