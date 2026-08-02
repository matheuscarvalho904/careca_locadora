<?php

namespace App\Traits;

use App\Contracts\TenantContext;
use App\Exceptions\MissingTenantContextException;
use App\Models\Organization;
use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function ($model): void {
            if (! empty($model->organization_id)) {
                return;
            }

            $organizationId = app(TenantContext::class)->id();

if ($organizationId === null) {
    $organizationId = Auth::user()?->organization_id;
}

if ($organizationId === null) {
    Log::error('TENANT DEBUG', [
        'user_id' => Auth::id(),
        'organization_id' => Auth::user()?->organization_id,
        'tenant_context_id' => app(TenantContext::class)->id(),
        'url' => request()->fullUrl(),
        'route' => optional(request()->route())->getName(),
        'model' => $model::class,
    ]);

    throw MissingTenantContextException::forModel($model::class);
}

$model->organization_id = $organizationId;
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeWithoutOrganizationScope($query)
    {
        return $query->withoutGlobalScope(OrganizationScope::class);
    }
}
