<?php
namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $guarded = [];
    protected $attributes = [
        'previous_received_quantity' => 0,
        'pending_quantity' => 0,
        'unit_value' => 0,
        'discount_value' => 0,
        'total_value' => 0,
        'accepted' => true,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->total_value = max(
                0,
                ((float) $item->received_quantity * (float) $item->unit_value)
                - (float) $item->discount_value
            );
        });
    }

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'decimal:4',
            'previous_received_quantity' => 'decimal:4',
            'received_quantity' => 'decimal:4',
            'pending_quantity' => 'decimal:4',
            'unit_value' => 'decimal:4',
            'discount_value' => 'decimal:2',
            'total_value' => 'decimal:2',
            'expires_at' => 'date',
            'accepted' => 'boolean',
        ];
    }

    public function receipt(): BelongsTo { return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id'); }
    public function purchaseOrderItem(): BelongsTo { return $this->belongsTo(PurchaseOrderItem::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
