<?php

namespace App\Models;

use App\Services\Fleet\AssetPrefixService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $asset): void {
            if (
                blank($asset->prefix)
                && filled($asset->organization_id)
                && filled($asset->category_id)
            ) {
                $asset->prefix = app(AssetPrefixService::class)->next(
                    organizationId: $asset->organization_id,
                    categoryId: $asset->category_id,
                );
            }
        });
    }

    protected function casts(): array
    {
        return [
            'manufacture_year' => 'integer',
            'model_year' => 'integer',
            'seats' => 'integer',
            'engine_displacement_cc' => 'integer',
            'engine_power_hp' => 'integer',
            'axles' => 'integer',
            'gross_weight_t' => 'decimal:3',
            'maximum_traction_capacity_t' => 'decimal:3',
            'fipe_value' => 'decimal:2',
            'fipe_score' => 'integer',
            'current_odometer' => 'decimal:2',
            'current_hourmeter' => 'decimal:2',
            'acquisition_date' => 'date',
            'acquisition_value' => 'decimal:2',
            'sale_date' => 'date',
            'sale_value' => 'decimal:2',
            'external_data' => 'array',
            'metadata' => 'array',
            'external_data_synced_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    public function rentalReservationItems(): HasMany
    {
        return $this->hasMany(RentalReservationItem::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AssetPhoto::class);
    }
}
