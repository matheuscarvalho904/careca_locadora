<?php

use Illuminate\Support\Facades\Route;

it('registra veículo público e criação da reserva', function (): void {
    expect(Route::has('api.public.vehicles.show'))->toBeTrue()
        ->and(Route::has('api.public.reservations.store'))->toBeTrue()
        ->and(Route::has('public.vehicles.show'))->toBeTrue();
});

it('possui serviço público integrado ao workflow real', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/PublicReservationService.php')
    );

    expect($service)
        ->toContain('RentalCommercialPricingService')
        ->toContain('ReservationWorkflow')
        ->toContain('resolveCustomer')
        ->toContain("'public_website'");
});

it('possui wizard responsivo e WhatsApp oficial', function (): void {
    $page = file_get_contents(
        resource_path('js/pages/public/vehicle-show.tsx')
    );

    expect($page)
        ->toContain('/api/public/vehicles/')
        ->toContain('/api/public/quote')
        ->toContain('/api/public/reservations')
        ->toContain('5562982887249')
        ->toContain('Confirmar reserva')
        ->toContain('commercial_item_ids');
});

it('remove o layout administrativo da página pública', function (): void {
    $app = file_get_contents(resource_path('js/app.tsx'));

    expect($app)->toContain("name.startsWith('public/')");
});
