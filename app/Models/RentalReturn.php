<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalReturn extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'draft',
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
        static::creating(function (self $return): void {
            if (blank($return->number) && filled($return->organization_id)) {
                $return->number = app(NumberSequenceService::class)->next(
                    organizationId: $return->organization_id,
                    key: 'rental_return',
                    name: 'Devoluções de locação',
                    prefix: 'DEV-',
                    padding: 7,
                );
            }
        });

        static::saving(function (self $return): void {
            $return->extra_time_value = (float) ($return->extra_time_value ?? 0);
            $return->mileage_value = (float) ($return->mileage_value ?? 0);
            $return->fuel_value = (float) ($return->fuel_value ?? 0);
            $return->damage_value = (float) ($return->damage_value ?? 0);
            $return->cleaning_value = (float) ($return->cleaning_value ?? 0);
            $return->missing_accessories_value = (float) ($return->missing_accessories_value ?? 0);
            $return->other_value = (float) ($return->other_value ?? 0);

            $return->total_charge_value =
                $return->extra_time_value
                + $return->mileage_value
                + $return->fuel_value
                + $return->damage_value
                + $return->cleaning_value
                + $return->missing_accessories_value
                + $return->other_value;
        });
    }

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'returned_at' => 'datetime',
            'extra_time_value' => 'decimal:2',
            'mileage_value' => 'decimal:2',
            'fuel_value' => 'decimal:2',
            'damage_value' => 'decimal:2',
            'cleaning_value' => 'decimal:2',
            'missing_accessories_value' => 'decimal:2',
            'other_value' => 'decimal:2',
            'total_charge_value' => 'decimal:2',
            'photos' => 'array',
            'metadata' => 'array',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class, 'contract_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(RentalDelivery::class, 'delivery_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalReturnItem::class, 'return_id');
    }

    public function rentalInvoice(): HasOne
    {
        return $this->hasOne(RentalInvoice::class, 'return_id');
    }
}
