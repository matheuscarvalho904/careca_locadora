<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationSupplier extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'invited',
        'freight_value' => 0,
        'discount_value' => 0,
        'additional_value' => 0,
        'subtotal' => 0,
        'total_value' => 0,
    ];

    protected static function booted(): void
    {
        static::saved(function (self $supplier): void {
            $subtotal = (float) $supplier->items()->sum('total_value');
            $total = max(
                0,
                $subtotal
                + (float) $supplier->freight_value
                + (float) $supplier->additional_value
                - (float) $supplier->discount_value
            );

            if (
                (float) $supplier->subtotal !== $subtotal
                || (float) $supplier->total_value !== $total
            ) {
                $supplier->updateQuietly([
                    'subtotal' => $subtotal,
                    'total_value' => $total,
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'proposal_date' => 'date',
            'proposal_valid_until' => 'date',
            'freight_value' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total_value' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(BusinessPartner::class, 'supplier_id'); }
    public function paymentCondition(): BelongsTo { return $this->belongsTo(PaymentCondition::class); }
    public function items(): HasMany { return $this->hasMany(QuotationSupplierItem::class); }
}
