<?php

it('usa método nativo do Eloquent nas consultas públicas', function (): void {
    $files = [
        app_path('Http/Controllers/Api/PublicCatalogController.php'),
        app_path('Http/Controllers/Api/PublicVehicleController.php'),
        app_path('Domain/Reservations/ReservationAvailabilityEngine.php'),
        app_path('Domain/Reservations/ReservationConflictEngine.php'),
        app_path('Services/Rentals/RentalCommercialPricingService.php'),
    ];

    foreach ($files as $file) {
        $source = file_get_contents($file);

        expect($source)
            ->not->toContain('withoutOrganizationScope()')
            ->toContain('withoutGlobalScopes()');
    }
});

it('mantém o isolamento explícito pela organização pública', function (): void {
    $catalog = file_get_contents(
        app_path('Http/Controllers/Api/PublicCatalogController.php')
    );

    $availability = file_get_contents(
        app_path('Domain/Reservations/ReservationAvailabilityEngine.php')
    );

    $pricing = file_get_contents(
        app_path('Services/Rentals/RentalCommercialPricingService.php')
    );

    expect($catalog)
        ->toContain("\$this->organizationId()")
        ->and($availability)
        ->toContain("where('organization_id', \$search->organizationId)")
        ->and($pricing)
        ->toContain("where('organization_id', \$search->organizationId)");
});

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
