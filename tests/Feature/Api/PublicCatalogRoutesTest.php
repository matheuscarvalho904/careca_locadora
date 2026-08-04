<?php

use Illuminate\Support\Facades\Route;

it('registra as rotas públicas reais do catálogo', function (): void {
    expect(Route::has('api.public.branches'))->toBeTrue()
        ->and(Route::has('api.public.categories'))->toBeTrue()
        ->and(Route::has('api.public.availability'))->toBeTrue()
        ->and(Route::has('api.public.quote'))->toBeTrue();
});

it('mantém os caminhos públicos corretos', function (): void {
    expect(route('api.public.branches', absolute: false))
        ->toBe('/api/public/branches')
        ->and(route('api.public.categories', absolute: false))
        ->toBe('/api/public/categories')
        ->and(route('api.public.availability', absolute: false))
        ->toBe('/api/public/availability')
        ->and(route('api.public.quote', absolute: false))
        ->toBe('/api/public/quote');
});
