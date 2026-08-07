<?php

it('expõe billing_unit e unit_value na cotação comercial', function (): void {
    $service = file_get_contents(app_path('Services/Rentals/RentalCommercialPricingService.php'));
    expect($service)
        ->toContain('$rate->billing_unit')
        ->toContain('$rate->unit_value');
});

it('usa file_path na foto pública', function (): void {
    $controller = file_get_contents(app_path('Http/Controllers/Api/PublicVehicleController.php'));
    expect($controller)
        ->toContain("'path' => \$photo->file_path")
        ->not->toContain("'path' => \$photo->path");
});

it('usa Branch.name na API pública do veículo', function (): void {
    $controller = file_get_contents(app_path('Http/Controllers/Api/PublicVehicleController.php'));
    expect($controller)
        ->toContain("'name' => \$vehicle->branch?->name")
        ->not->toContain('$vehicle->branch?->trade_name');
});
