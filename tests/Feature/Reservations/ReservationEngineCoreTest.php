<?php

it('possui objetos de entrada e cotação', function (): void {
    expect(file_exists(
        app_path('Data/Rentals/ReservationSearch.php')
    ))->toBeTrue()
        ->and(file_exists(
            app_path('Data/Rentals/ReservationQuote.php')
        ))->toBeTrue();
});

it('possui os motores centrais de reserva', function (): void {
    $files = [
        'ReservationConflictEngine.php',
        'ReservationAvailabilityEngine.php',
        'ReservationPricingEngine.php',
        'ReservationAssignmentEngine.php',
        'ReservationQuoteEngine.php',
        'ReservationWorkflow.php',
    ];

    foreach ($files as $file) {
        expect(file_exists(
            app_path("Domain/Reservations/{$file}")
        ))->toBeTrue();
    }
});

it('reutiliza os status bloqueadores existentes', function (): void {
    $engine = file_get_contents(
        app_path(
            'Domain/Reservations/ReservationConflictEngine.php'
        )
    );

    expect($engine)
        ->toContain('RentalAvailabilityService::BLOCKING_STATUSES')
        ->toContain("where('starts_at', '<'")
        ->toContain("where('ends_at', '>'");
});

it('suporta consulta por categoria filial e ativo', function (): void {
    $engine = file_get_contents(
        app_path(
            'Domain/Reservations/ReservationAvailabilityEngine.php'
        )
    );

    expect($engine)
        ->toContain("where('branch_id'")
        ->toContain("where('category_id'")
        ->toContain('whereKey($search->assetId)')
        ->toContain('categorySummary');
});

it('suporta hora diária semanal mensal e valor fechado', function (): void {
    $engine = file_get_contents(
        app_path(
            'Domain/Reservations/ReservationPricingEngine.php'
        )
    );

    expect($engine)
        ->toContain("'hourly'")
        ->toContain("'daily'")
        ->toContain("'weekly'")
        ->toContain("'monthly'")
        ->toContain("'fixed'");
});

it('cria reserva em transação e reaproveita o serviço atual', function (): void {
    $workflow = file_get_contents(
        app_path('Domain/Reservations/ReservationWorkflow.php')
    );

    expect($workflow)
        ->toContain('DB::transaction')
        ->toContain('RentalReservation::query()->create')
        ->toContain('$reservation->items()->create')
        ->toContain('$this->reservations->recalculate');
});
