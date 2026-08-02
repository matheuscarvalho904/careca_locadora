<?php

namespace App\Scopes;

use App\Contracts\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $organizationId = app(TenantContext::class)->id();

        if ($organizationId === null) {
            return;
        }

        $builder->where(
            $model->qualifyColumn('organization_id'),
            $organizationId
        );
    }
}
