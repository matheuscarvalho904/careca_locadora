<?php

namespace App\Domain\Reservations;

use App\Data\Rentals\ReservationSearch;
use App\Models\Asset;
use Illuminate\Support\Collection;

final class ReservationAssignmentEngine
{
    public function __construct(
        private readonly ReservationAvailabilityEngine $availability,
    ) {
    }

    public function suggestions(
        ReservationSearch $search,
        int $limit = 5,
    ): Collection {
        return $this->availability
            ->availableAssets($search, limit: max(1, min(50, $limit)))
            ->sortBy(function (Asset $asset): string {
                $odometer = str_pad(
                    (string) ((int) ($asset->current_odometer ?? 0)),
                    12,
                    '0',
                    STR_PAD_LEFT
                );

                $hourmeter = str_pad(
                    (string) ((int) ($asset->current_hourmeter ?? 0)),
                    12,
                    '0',
                    STR_PAD_LEFT
                );

                return "{$odometer}:{$hourmeter}:{$asset->prefix}";
            })
            ->values();
    }

    public function best(ReservationSearch $search): ?Asset
    {
        return $this->suggestions($search, 1)->first();
    }
}
