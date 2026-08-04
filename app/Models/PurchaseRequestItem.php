<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->estimated_total = max(
                0,
                (float) $item->quantity * (float) $item->estimated_unit_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'estimated_unit_value' => 'decimal:4',
            'estimated_total' => 'decimal:2',
            'meter_reading' => 'decimal:2',
            'meter_recorded_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo { return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function applicationCenter(): BelongsTo { return $this->belongsTo(ApplicationCenter::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function costCenter(): BelongsTo { return $this->belongsTo(CostCenter::class); }
}
