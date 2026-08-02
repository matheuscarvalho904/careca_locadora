<?php

use App\Data\Rentals\ReservationPeriod;
use InvalidArgumentException;

it('detecta períodos sobrepostos', function (): void {
    $first = new ReservationPeriod(
        '2026-08-01 08:00:00',
        '2026-08-03 08:00:00',
    );

    $second = new ReservationPeriod(
        '2026-08-02 08:00:00',
        '2026-08-04 08:00:00',
    );

    expect($first->overlaps($second))->toBeTrue();
});

it('permite períodos encostados sem conflito', function (): void {
    $first = new ReservationPeriod(
        '2026-08-01 08:00:00',
        '2026-08-03 08:00:00',
    );

    $second = new ReservationPeriod(
        '2026-08-03 08:00:00',
        '2026-08-04 08:00:00',
    );

    expect($first->overlaps($second))->toBeFalse();
});

it('rejeita devolução anterior ou igual à retirada', function (): void {
    new ReservationPeriod(
        '2026-08-03 08:00:00',
        '2026-08-03 08:00:00',
    );
})->throws(InvalidArgumentException::class);
