<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalInvoiceItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->quantity = (float) ($item->quantity ?? 1);
            $item->unit_value = (float) ($item->unit_value ?? 0);
            $item->discount_value = (float) ($item->discount_value ?? 0);
            $item->additional_value = (float) ($item->additional_value ?? 0);

            $item->total_value = max(
                0,
                ($item->quantity * $item->unit_value)
                - $item->discount_value
                + $item->additional_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_value' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(RentalInvoice::class, 'invoice_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
