<?php
it('possui API publica de catalogo integrada aos motores', function (): void {
    $controller = file_get_contents(
        app_path('Http/Controllers/Api/PublicCatalogController.php')
    );
    expect($controller)
        ->toContain('ReservationAvailabilityEngine')
        ->toContain('RentalCommercialPricingService')
        ->toContain('function branches')
        ->toContain('function categories')
        ->toContain('function availability')
        ->toContain('function quote');
});
it('registra endpoints publicos', function (): void {
    $routes = file_get_contents(base_path('routes/api-public-catalog.php'));
    expect($routes)
        ->toContain('/branches')
        ->toContain('/categories')
        ->toContain('/availability')
        ->toContain('/quote');
});
