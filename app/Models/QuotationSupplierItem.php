<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationSupplierItem extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $attributes = [
        'unit_value' => 0,
        'discount_value' => 0,
        'total_value' => 0,
        'is_selected' => false,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $quantity = (float) ($item->quotationItem?->quantity ?? 0);

            $item->total_value = max(
                0,
                ($quantity * (float) $item->unit_value)
                - (float) $item->discount_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'unit_value' => 'decimal:4',
            'discount_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'is_selected' => 'boolean',
        ];
    }

    public function quotationSupplier(): BelongsTo { return $this->belongsTo(QuotationSupplier::class); }
    public function quotationItem(): BelongsTo { return $this->belongsTo(QuotationItem::class); }
}
