<?php

namespace App\Domain\Reservations;

use App\Data\Rentals\ReservationQuote;
use App\Data\Rentals\ReservationSearch;
use Illuminate\Support\Collection;

final class ReservationQuoteEngine
{
    public function __construct(
        private readonly ReservationAvailabilityEngine $availability,
        private readonly ReservationPricingEngine $pricing,
    ) {
    }

    public function quote(
        ReservationSearch $search,
        string $billingUnit,
        float $unitValue,
        float $discountValue = 0,
        float $additionalValue = 0,
        float $depositValue = 0,
    ): array {
        $quote = $this->pricing->quote(
            startsAt: $search->startsAt,
            endsAt: $search->endsAt,
            billingUnit: $billingUnit,
            unitValue: $unitValue,
            discountValue: $discountValue,
            additionalValue: $additionalValue,
            depositValue: $depositValue,
        );

        return [
            'quote' => $quote->toArray(),
            'categories' => $this->availability
                ->categorySummary($search)
                ->all(),
            'available_assets' => $this->availability
                ->availableAssets($search, limit: 50)
                ->map(fn ($asset): array => [
                    'id' => $asset->id,
                    'category_id' => $asset->category_id,
                    'category' => $asset->category?->name,
                    'prefix' => $asset->prefix,
                    'name' => $asset->name,
                    'plate' => $asset->plate,
                    'branch_id' => $asset->branch_id,
                ])
                ->all(),
        ];
    }
}
