<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationItem extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:4'];
    }

    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function purchaseRequestItem(): BelongsTo { return $this->belongsTo(PurchaseRequestItem::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function applicationCenter(): BelongsTo { return $this->belongsTo(ApplicationCenter::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function costCenter(): BelongsTo { return $this->belongsTo(CostCenter::class); }
    public function supplierItems(): HasMany { return $this->hasMany(QuotationSupplierItem::class); }
}
