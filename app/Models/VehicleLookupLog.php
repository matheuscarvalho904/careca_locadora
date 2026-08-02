<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLookupLog extends Model
{
    use BelongsToOrganization;
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'duration_ms' => 'integer',
            'consulted_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
