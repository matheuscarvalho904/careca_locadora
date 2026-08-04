<?php

use App\Http\Controllers\Api\PublicCatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')
    ->name('api.public.')
    ->group(function (): void {
        Route::get('/branches', [
            PublicCatalogController::class,
            'branches',
        ])->name('branches');

        Route::get('/categories', [
            PublicCatalogController::class,
            'categories',
        ])->name('categories');

        Route::post('/availability', [
            PublicCatalogController::class,
            'availability',
        ])->name('availability');

        Route::post('/quote', [
            PublicCatalogController::class,
            'quote',
        ])->name('quote');
    });
