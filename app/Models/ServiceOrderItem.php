<?php

namespace App\Models;

use App\Services\Procurement\ProcurementValidationService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderItem extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->service_id = null;

            $item->service_description = trim(
                (string) $item->service_description
            );

            $item->quantity = (float) ($item->quantity ?? 0);
            $item->unit_value = (float) ($item->unit_value ?? 0);
            $item->discount_value = (float) ($item->discount_value ?? 0);

            app(ProcurementValidationService::class)->validateServiceItem(
                $item->toArray()
            );

            $item->total_value = max(
                0,
                ($item->quantity * $item->unit_value)
                - $item->discount_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_value' => 'decimal:4',
            'discount_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'meter_reading' => 'decimal:2',
            'meter_recorded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            ServiceOrder::class,
            'service_order_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function applicationCenter(): BelongsTo
    {
        return $this->belongsTo(ApplicationCenter::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
