<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::middleware([
    'auth',
    \App\Http\Middleware\ResolveTenantContext::class,
])->get(
    '/financeiro/faturas-de-locacao/{invoice}/pdf',
    \App\Http\Controllers\Finance\RentalInvoicePdfController::class
)->name('rental-invoices.pdf');

