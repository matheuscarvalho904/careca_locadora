<?php

use App\Http\Controllers\Api\PublicReservationController;
use App\Http\Controllers\Api\PublicVehicleController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')
    ->name('api.public.')
    ->group(function (): void {
        Route::get('/vehicles/{asset}', [
            PublicVehicleController::class,
            'show',
        ])->name('vehicles.show');

        Route::post('/reservations', [
            PublicReservationController::class,
            'store',
        ])->name('reservations.store');
    });
