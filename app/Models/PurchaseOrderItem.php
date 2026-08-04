<?php

namespace App\Models;

use App\Services\Procurement\ProcurementValidationService;
use App\Services\Procurement\PurchaseOrderTotalsService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            app(ProcurementValidationService::class)->validatePurchaseItem(
                $item->toArray()
            );

            $item->total_value = max(
                0,
                ((float) $item->quantity * (float) $item->unit_value)
                - (float) $item->discount_value
            );
        });
        static::saved(function (self $item): void {
            if ($item->order) {
                app(PurchaseOrderTotalsService::class)->recalculate($item->order);
            }
        });

        static::deleted(function (self $item): void {
            if ($item->order) {
                app(PurchaseOrderTotalsService::class)->recalculate($item->order);
            }
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
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function applicationCenter(): BelongsTo
    {
        return $this->belongsTo(ApplicationCenter::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
