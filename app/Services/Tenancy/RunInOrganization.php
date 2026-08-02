<?php

namespace App\Services\Tenancy;

use App\Contracts\TenantContext;
use App\Models\Organization;
use Closure;
use Spatie\Permission\PermissionRegistrar;

final class RunInOrganization
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {
    }

    public function execute(
        Organization $organization,
        Closure $callback
    ): mixed {
        $previousOrganization = $this->tenantContext->organization();
        $previousTeamId = $this->permissionRegistrar->getPermissionsTeamId();

        $this->tenantContext->set($organization);
        $this->permissionRegistrar->setPermissionsTeamId(
            $organization->getKey()
        );

        try {
            return $callback();
        } finally {
            $this->tenantContext->set($previousOrganization);
            $this->permissionRegistrar->setPermissionsTeamId(
                $previousTeamId
            );
        }
    }
}
