<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalReservation extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'draft',
        'subtotal' => 0,
        'discount_value' => 0,
        'additional_value' => 0,
        'deposit_value' => 0,
        'total_value' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $reservation): void {
            if (blank($reservation->number) && filled($reservation->organization_id)) {
                $reservation->number = app(NumberSequenceService::class)->next(
                    organizationId: $reservation->organization_id,
                    key: 'rental_reservation',
                    name: 'Reservas de locação',
                    prefix: 'RES-',
                    padding: 7,
                );
            }

            $reservation->reserved_at ??= now();
        });

        static::saving(function (self $reservation): void {
            $reservation->subtotal = (float) ($reservation->subtotal ?? 0);
            $reservation->discount_value = (float) ($reservation->discount_value ?? 0);
            $reservation->additional_value = (float) ($reservation->additional_value ?? 0);
            $reservation->deposit_value = (float) ($reservation->deposit_value ?? 0);

            $reservation->total_value = max(
                0,
                (float) $reservation->subtotal
                - (float) $reservation->discount_value
                + (float) $reservation->additional_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
            'pickup_expected_at' => 'datetime',
            'return_expected_at' => 'datetime',
            'pickup_actual_at' => 'datetime',
            'return_actual_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'deposit_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'business_partner_id');
    }

    public function authorizedContact(): BelongsTo
    {
        return $this->belongsTo(
            BusinessPartnerContact::class,
            'authorized_contact_id'
        );
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            RentalReservationItem::class,
            'reservation_id'
        );
    }

    public function isBlockingAvailability(): bool
    {
        return in_array(
            $this->status,
            \App\Services\Rentals\RentalAvailabilityService::BLOCKING_STATUSES,
            true,
        );
    }
}
