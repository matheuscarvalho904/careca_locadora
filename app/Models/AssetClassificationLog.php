<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetClassificationLog extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'confidence' => 'integer',
            'auto_applied' => 'boolean',
            'matched_fields' => 'array',
            'source_data' => 'array',
            'classified_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AssetClassificationRule::class, 'rule_id');
    }

    public function suggestedCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'suggested_category_id');
    }
}
