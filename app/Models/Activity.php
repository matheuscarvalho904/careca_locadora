<?php

namespace App\Models;

use App\Contracts\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    protected static function booted(): void
    {
        static::creating(function (self $activity): void {
            if (! empty($activity->organization_id)) {
                return;
            }

            $activity->organization_id = app(TenantContext::class)->id();
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
