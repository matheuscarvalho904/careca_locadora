<?php

namespace App\Models;

use App\Services\Rentals\RentalAvailabilityService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class RentalReservationItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            $reservation = $item->reservation;

            $item->organization_id ??= $reservation?->organization_id;
            $item->starts_at ??= $reservation?->pickup_expected_at;
            $item->ends_at ??= $reservation?->return_expected_at;
        });

        static::saving(function (self $item): void {
            if (filled($item->starts_at) && filled($item->ends_at)) {
                app(RentalAvailabilityService::class)->assertAvailable(
                    organizationId: (string) $item->organization_id,
                    assetId: (string) $item->asset_id,
                    startsAt: $item->starts_at,
                    endsAt: $item->ends_at,
                    ignoreReservationId: $item->reservation_id,
                );
            }

            $item->total_value = max(
                0,
                ((float) $item->quantity * (float) $item->unit_value)
                - (float) $item->discount_value
                + (float) $item->additional_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'quantity' => 'decimal:3',
            'unit_value' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'expected_initial_odometer' => 'decimal:2',
            'expected_initial_hourmeter' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            RentalReservation::class,
            'reservation_id'
        );
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
