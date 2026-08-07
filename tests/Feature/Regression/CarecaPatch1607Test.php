<?php

it('não depende de items_total inexistente na cotação comercial', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/PublicReservationService.php')
    );

    expect($service)
        ->not->toContain("\$quote['items_total']")
        ->toContain("collect(\$quote['commercial_items'] ?? [])->sum('total')");
});

it('trata coupon e valores opcionais de forma defensiva', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/PublicReservationService.php')
    );

    expect($service)
        ->toContain("\$quote['coupon'] ?? null")
        ->toContain("\$quote['coupon_discount'] ?? 0")
        ->toContain("\$quote['deposit_value'] ?? 0");
});
