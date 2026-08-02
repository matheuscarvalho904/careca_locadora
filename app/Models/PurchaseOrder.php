<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $attributes = [
        'origin_type' => 'direct',
        'status' => 'draft',
        'installments' => 1,
        'installment_interval_days' => 30,
        'subtotal' => 0,
        'discount_value' => 0,
        'freight_value' => 0,
        'additional_value' => 0,
        'total_value' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (blank($order->number) && filled($order->organization_id)) {
                $order->number = app(NumberSequenceService::class)->next(
                    organizationId: $order->organization_id,
                    key: 'purchase_order',
                    name: 'Ordens de compra',
                    prefix: 'OC-',
                    padding: 8,
                );
            }
        });

        static::saved(function (self $order): void {
            $subtotal = (float) $order->items()->sum('total_value');
            $total = max(
                0,
                $subtotal
                + (float) $order->freight_value
                + (float) $order->additional_value
                - (float) $order->discount_value
            );

            if (
                (float) $order->subtotal !== $subtotal
                || (float) $order->total_value !== $total
            ) {
                $order->updateQuietly([
                    'subtotal' => $subtotal,
                    'total_value' => $total,
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expected_delivery_at' => 'date',
            'first_due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'freight_value' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'total_value' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id');
    }

    public function paymentCondition(): BelongsTo
    {
        return $this->belongsTo(PaymentCondition::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
