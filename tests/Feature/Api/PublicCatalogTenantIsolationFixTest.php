<?php

it('remove o escopo global nas consultas públicas de lojas e categorias', function (): void {
    $controller = file_get_contents(
        app_path('Http/Controllers/Api/PublicCatalogController.php')
    );

    expect(substr_count($controller, 'withoutOrganizationScope()'))
        ->toBeGreaterThanOrEqual(2)
        ->and($controller)
        ->toContain("\$this->organizationId()");
});

it('mantém filtro explícito por organização nos ativos públicos', function (): void {
    $availability = file_get_contents(
        app_path(
            'Domain/Reservations/ReservationAvailabilityEngine.php'
        )
    );

    $vehicle = file_get_contents(
        app_path('Http/Controllers/Api/PublicVehicleController.php')
    );

    expect($availability)
        ->toContain('withoutOrganizationScope()')
        ->toContain("where('organization_id', \$search->organizationId)")
        ->and($vehicle)
        ->toContain('withoutOrganizationScope()')
        ->toContain("where('organization_id', \$organizationId)");
});

it('corrige as quatro consultas do motor comercial público', function (): void {
    $pricing = file_get_contents(
        app_path(
            'Services/Rentals/RentalCommercialPricingService.php'
        )
    );

    expect(substr_count($pricing, 'withoutOrganizationScope()'))
        ->toBeGreaterThanOrEqual(4)
        ->and($pricing)
        ->toContain("RentalRatePlan::query()")
        ->toContain("RentalCommercialItem::query()")
        ->toContain("RentalCoupon::query()")
        ->toContain("RentalCommercialRule::query()");
});

it('consulta conflitos sem depender do tenant autenticado', function (): void {
    $conflicts = file_get_contents(
        app_path(
            'Domain/Reservations/ReservationConflictEngine.php'
        )
    );

    expect($conflicts)
        ->toContain('withoutOrganizationScope()')
        ->toContain("where('organization_id', \$search->organizationId)");
});
