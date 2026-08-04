<?php

namespace App\Domain\Reservations;

use App\Data\Rentals\ReservationQuote;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class ReservationPricingEngine
{
    public function quote(
        Carbon|string $startsAt,
        Carbon|string $endsAt,
        string $billingUnit,
        float $unitValue,
        float $discountValue = 0,
        float $additionalValue = 0,
        float $depositValue = 0,
    ): ReservationQuote {
        $startsAt = Carbon::parse($startsAt);
        $endsAt = Carbon::parse($endsAt);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'period' => 'A devolução deve ser posterior à retirada.',
            ]);
        }

        $quantity = $this->quantity($startsAt, $endsAt, $billingUnit);
        $subtotal = round(max(0, $quantity * max(0, $unitValue)), 2);
        $discountValue = round(max(0, $discountValue), 2);
        $additionalValue = round(max(0, $additionalValue), 2);
        $depositValue = round(max(0, $depositValue), 2);
        $total = round(
            max(0, $subtotal - $discountValue + $additionalValue),
            2
        );

        return new ReservationQuote(
            billingUnit: $billingUnit,
            quantity: $quantity,
            unitValue: round(max(0, $unitValue), 2),
            subtotal: $subtotal,
            discountValue: $discountValue,
            additionalValue: $additionalValue,
            depositValue: $depositValue,
            totalValue: $total,
            breakdown: [
                'duration_minutes' => $startsAt->diffInMinutes($endsAt),
                'duration_hours' => round(
                    $startsAt->diffInMinutes($endsAt) / 60,
                    3
                ),
                'deposit_payable_separately' => true,
            ],
        );
    }

    public function quantity(
        Carbon|string $startsAt,
        Carbon|string $endsAt,
        string $billingUnit,
    ): float {
        $startsAt = Carbon::parse($startsAt);
        $endsAt = Carbon::parse($endsAt);
        $minutes = max(1, $startsAt->diffInMinutes($endsAt));

        return match ($billingUnit) {
            'hourly' => (float) max(1, (int) ceil($minutes / 60)),
            'daily' => (float) max(1, (int) ceil($minutes / 1440)),
            'weekly' => (float) max(1, (int) ceil($minutes / 10080)),
            'monthly' => (float) max(1, (int) ceil($minutes / 43200)),
            'fixed' => 1.0,
            default => throw ValidationException::withMessages([
                'billing_unit' => 'Unidade de cobrança inválida.',
            ]),
        };
    }
}
