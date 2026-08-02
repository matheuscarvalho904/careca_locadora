<?php

namespace App\Services\Rentals;

use App\Models\RentalReservation;
use Illuminate\Support\Facades\DB;

final class RentalReservationService
{
    public function recalculate(RentalReservation $reservation): void
    {
        DB::transaction(function () use ($reservation): void {
            $subtotal = (float) $reservation->items()->sum('total_value');

            $reservation->forceFill([
                'subtotal' => $subtotal,
                'total_value' => max(
                    0,
                    $subtotal
                    - (float) $reservation->discount_value
                    + (float) $reservation->additional_value
                ),
            ])->saveQuietly();

            $this->synchronizeAssetStatuses($reservation);
        });
    }

    public function synchronizeAssetStatuses(
        RentalReservation $reservation,
    ): void {
        $assetIds = $reservation->items()->pluck('asset_id');

        if ($assetIds->isEmpty()) {
            return;
        }

        $status = match ($reservation->status) {
            'confirmed', 'preparing' => 'reserved',
            'converted' => 'rented',
            default => null,
        };

        if ($status !== null) {
            $reservation->items()
                ->with('asset')
                ->get()
                ->each(
                    fn ($item) => $item->asset?->update([
                        'rental_status' => $status,
                    ])
                );

            return;
        }

        $reservation->items()
            ->with('asset')
            ->get()
            ->each(function ($item): void {
                $hasOtherBlockingReservation = $item->asset
                    ?->rentalReservationItems()
                    ->where('reservation_id', '!=', $item->reservation_id)
                    ->whereHas('reservation', fn ($query) =>
                        $query->whereIn('status', [
                            'confirmed',
                            'preparing',
                            'converted',
                        ])
                    )
                    ->exists();

                if (! $hasOtherBlockingReservation) {
                    $item->asset?->update([
                        'rental_status' => 'available',
                    ]);
                }
            });
    }
}
