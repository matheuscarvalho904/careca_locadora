<?php

it('mantem a home publica no Inertia', function (): void {
    $routes = file_get_contents(base_path('routes/web.php'));
    $app = file_get_contents(resource_path('js/app.tsx'));

    expect($routes)
        ->toContain("Route::inertia('/', 'welcome')")
        ->and($app)
        ->toContain("case name === 'welcome'")
        ->toContain('return null');
});

it('integra busca catalogo disponibilidade e cotacao', function (): void {
    $page = file_get_contents(resource_path('js/pages/welcome.tsx'));

    expect($page)
        ->toContain('/api/public/branches')
        ->toContain('/api/public/categories')
        ->toContain('/api/public/availability')
        ->toContain('/api/public/quote')
        ->toContain('Pesquisar veículos')
        ->toContain('Calcular valor');
});

it('possui identidade e responsividade', function (): void {
    $page = file_get_contents(resource_path('js/pages/welcome.tsx'));

    expect($page)
        ->toContain('Careca Locadora')
        ->toContain('Área do cliente')
        ->toContain('Painel administrativo')
        ->toContain('lg:grid-cols')
        ->toContain('md:grid-cols')
        ->toContain('lg:hidden');
});
