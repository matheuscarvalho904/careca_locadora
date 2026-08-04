<?php

it('mantém as rotas públicas registradas', function (): void {
    expect(route('api.public.branches', absolute: false))
        ->toBe('/api/public/branches')
        ->and(route('api.public.categories', absolute: false))
        ->toBe('/api/public/categories')
        ->and(route('api.public.availability', absolute: false))
        ->toBe('/api/public/availability')
        ->and(route('api.public.quote', absolute: false))
        ->toBe('/api/public/quote');
});
