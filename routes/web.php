<?php

use App\Http\Controllers\ProductionOrderPdfController;
use App\Http\Controllers\TimeClockController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::middleware(['auth'])->group(function () {
    Route::get('/production-orders/{uuid}/pdf', [ProductionOrderPdfController::class, 'generatePdf'])
        ->name('production-orders.pdf');

    Route::get('/map-register-point/{actionType}', function (string $actionType) {
        // Valide $actionType se necessário (ex: in_array($actionType, ['clock_in', ...]))
        return view('livewire.time-clock.map-register-point', ['actionType' => $actionType]);
    })->name('time-clock.map-register-point');

// Rota para receber os dados da batida de ponto (POST request do JavaScript)
    Route::post('/time-clock/store', [TimeClockController::class, 'store'])->name('time-clock.store');
});
