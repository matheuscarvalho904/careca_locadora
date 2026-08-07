<?php

namespace App\Domain\Reservations;

use App\Data\Rentals\ReservationSearch;
use App\Models\RentalReservationItem;
use App\Services\Rentals\RentalAvailabilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ReservationConflictEngine
{
    public function forAsset(
        ReservationSearch $search,
        string $assetId,
    ): Collection {
        return RentalReservationItem::query()
            ->withoutGlobalScopes()
            ->with(['reservation.customer', 'asset'])
            ->where('organization_id', $search->organizationId)
            ->where('asset_id', $assetId)
            ->when(
                filled($search->ignoreReservationId),
                fn (Builder $query): Builder => $query->where(
                    'reservation_id',
                    '!=',
                    $search->ignoreReservationId
                )
            )
            ->where('starts_at', '<', $search->effectiveEndsAt())
            ->where('ends_at', '>', $search->effectiveStartsAt())
            ->whereHas(
                'reservation',
                fn (Builder $query): Builder => $query->whereIn(
                    'status',
                    RentalAvailabilityService::BLOCKING_STATUSES
                )
            )
            ->orderBy('starts_at')
            ->get();
    }

    public function hasConflict(
        ReservationSearch $search,
        string $assetId,
    ): bool {
        return $this->forAsset($search, $assetId)->isNotEmpty();
    }
}
