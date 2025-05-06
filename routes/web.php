<?php

use App\Http\Controllers\ProductionOrderPdfController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::middleware(['auth'])->group(function () {
    Route::get('/production-orders/{uuid}/pdf', [ProductionOrderPdfController::class, 'generatePdf'])
        ->name('production-orders.pdf');
});
