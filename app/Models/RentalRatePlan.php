<?php
namespace App\Models; use App\Traits\BelongsToOrganization; use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class RentalRatePlan extends Model { use BelongsToOrganization,HasFactory,HasUuids,SoftDeletes; protected $guarded=[]; protected $attributes=['billing_unit'=>'daily','unit_value'=>0,'included_distance'=>null,'included_hours'=>null,'extra_distance_value'=>0,'extra_hour_value'=>0,'deposit_value'=>0,'minimum_value'=>0,'minimum_quantity'=>1,'priority'=>100,'status'=>'active']; protected function casts():array{return ['unit_value'=>'decimal:2','included_distance'=>'decimal:2','included_hours'=>'decimal:2','extra_distance_value'=>'decimal:4','extra_hour_value'=>'decimal:4','deposit_value'=>'decimal:2','minimum_value'=>'decimal:2','minimum_quantity'=>'integer','priority'=>'integer','valid_from'=>'date','valid_until'=>'date','weekdays'=>'array','metadata'=>'array'];}
    public function assetCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(
            \App\Models\AssetCategory::class,
            'asset_category_id'
        );
    }
    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $rate): void {
            $rate->normalizeRequiredNumericFields();
        });
    }

    private function normalizeRequiredNumericFields(): void
    {
        foreach ([
            'unit_value',
            'extra_distance_value',
            'extra_hour_value',
            'deposit_value',
            'minimum_value',
        ] as $field) {
            if ($this->getAttribute($field) === null || $this->getAttribute($field) === '') {
                $this->setAttribute($field, 0);
            }
        }

        if ($this->getAttribute('minimum_quantity') === null || $this->getAttribute('minimum_quantity') === '') {
            $this->setAttribute('minimum_quantity', 1);
        }

        if ($this->getAttribute('priority') === null || $this->getAttribute('priority') === '') {
            $this->setAttribute('priority', 100);
        }
    }
}
