<?php

namespace App\Models;

use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'product_type' => 'product',
        'stock_controlled' => true,
        'allow_negative_stock' => false,
        'batch_controlled' => false,
        'expiry_controlled' => false,
        'serial_controlled' => false,
        'asset_controlled' => false,
        'minimum_stock' => 0,
        'maximum_stock' => 0,
        'reorder_point' => 0,
        'minimum_purchase_quantity' => 0,
        'purchase_multiple' => 1,
        'lead_time_days' => 0,
        'average_cost' => 0,
        'last_purchase_cost' => 0,
        'target_cost' => 0,
        'status' => 'active',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $product): void {
            if (blank($product->code) && filled($product->organization_id)) {
                $product->code = app(NumberSequenceService::class)->next(
                    organizationId: $product->organization_id,
                    key: 'product',
                    name: 'Produtos',
                    prefix: 'PROD-',
                    padding: 6,
                );
            }
        });
    }

    protected function casts(): array
    {
        return [
            'stock_controlled' => 'boolean',
            'allow_negative_stock' => 'boolean',
            'batch_controlled' => 'boolean',
            'expiry_controlled' => 'boolean',
            'serial_controlled' => 'boolean',
            'asset_controlled' => 'boolean',
            'minimum_stock' => 'decimal:4',
            'maximum_stock' => 'decimal:4',
            'reorder_point' => 'decimal:4',
            'minimum_purchase_quantity' => 'decimal:4',
            'purchase_multiple' => 'decimal:4',
            'average_cost' => 'decimal:4',
            'last_purchase_cost' => 'decimal:4',
            'target_cost' => 'decimal:4',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    public function primarySupplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'primary_supplier_id');
    }

    public function defaultApplicationCenter(): BelongsTo
    {
        return $this->belongsTo(
            ApplicationCenter::class,
            'default_application_center_id'
        );
    }

    public function defaultCostCenter(): BelongsTo
    {
        return $this->belongsTo(
            CostCenter::class,
            'default_cost_center_id'
        );
    }
}
