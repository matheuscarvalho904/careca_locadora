<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalReturnItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $attributes = [
        'extra_time_value' => 0,
        'mileage_value' => 0,
        'fuel_value' => 0,
        'damage_value' => 0,
        'cleaning_value' => 0,
        'missing_accessories_value' => 0,
        'other_value' => 0,
        'total_charge_value' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->distance_used = self::difference(
                $item->initial_odometer,
                $item->final_odometer,
            );

            $item->hours_used = self::difference(
                $item->initial_hourmeter,
                $item->final_hourmeter,
            );

            $item->extra_time_value = (float) ($item->extra_time_value ?? 0);
            $item->mileage_value = (float) ($item->mileage_value ?? 0);
            $item->fuel_value = (float) ($item->fuel_value ?? 0);
            $item->damage_value = (float) ($item->damage_value ?? 0);
            $item->cleaning_value = (float) ($item->cleaning_value ?? 0);
            $item->missing_accessories_value = (float) ($item->missing_accessories_value ?? 0);
            $item->other_value = (float) ($item->other_value ?? 0);

            $item->total_charge_value =
                $item->extra_time_value
                + $item->mileage_value
                + $item->fuel_value
                + $item->damage_value
                + $item->cleaning_value
                + $item->missing_accessories_value
                + $item->other_value;
        });
    }

    protected function casts(): array
    {
        return [
            'initial_odometer' => 'decimal:2',
            'final_odometer' => 'decimal:2',
            'distance_used' => 'decimal:2',
            'initial_hourmeter' => 'decimal:2',
            'final_hourmeter' => 'decimal:2',
            'hours_used' => 'decimal:2',
            'body_ok' => 'boolean',
            'tires_ok' => 'boolean',
            'lights_ok' => 'boolean',
            'glass_ok' => 'boolean',
            'documents_ok' => 'boolean',
            'accessories_ok' => 'boolean',
            'cleanliness_ok' => 'boolean',
            'primary_key_returned' => 'boolean',
            'spare_key_returned' => 'boolean',
            'manual_returned' => 'boolean',
            'extra_time_value' => 'decimal:2',
            'mileage_value' => 'decimal:2',
            'fuel_value' => 'decimal:2',
            'damage_value' => 'decimal:2',
            'cleaning_value' => 'decimal:2',
            'missing_accessories_value' => 'decimal:2',
            'other_value' => 'decimal:2',
            'total_charge_value' => 'decimal:2',
            'photos' => 'array',
            'checklist' => 'array',
        ];
    }

    public function rentalReturn(): BelongsTo
    {
        return $this->belongsTo(RentalReturn::class, 'return_id');
    }

    public function deliveryItem(): BelongsTo
    {
        return $this->belongsTo(RentalDeliveryItem::class, 'delivery_item_id');
    }

    public function contractItem(): BelongsTo
    {
        return $this->belongsTo(RentalContractItem::class, 'contract_item_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    private static function difference(mixed $initial, mixed $final): ?float
    {
        if ($initial === null || $final === null) {
            return null;
        }

        return max(0, (float) $final - (float) $initial);
    }
}
