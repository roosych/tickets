<?php

use App\Http\Controllers\Api\IntegrationTicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API для внешних систем (ServiceRadar и т.п.)
|--------------------------------------------------------------------------
| Доступ по токену технического пользователя (php artisan integration:robot) со способностью tickets:integration.
*/

Route::middleware(['auth:sanctum', 'abilities:'.IntegrationTicketController::ABILITY])
    ->prefix('v1/integration')
    ->name('api.integration.')
    ->group(function () {
        Route::get('tickets', [IntegrationTicketController::class, 'index'])->name('tickets.index');
        Route::post('tickets', [IntegrationTicketController::class, 'store'])->name('tickets.store');
        Route::get('tickets/{ticket}', [IntegrationTicketController::class, 'show'])->name('tickets.show');
        Route::patch('tickets/{ticket}', [IntegrationTicketController::class, 'update'])->name('tickets.update');
        Route::post('tickets/{ticket}/status', [IntegrationTicketController::class, 'status'])->name('tickets.status');
    });
