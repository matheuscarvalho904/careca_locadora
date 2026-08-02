<?php

namespace App\Http\Middleware;

use App\Contracts\TenantContext;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenantContext
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            $this->clearContext();

            return $next($request);
        }

        $organization = $user->organization;

        if ($organization === null && ! $user->is_platform_admin) {
            $this->clearContext();

            abort(
                Response::HTTP_FORBIDDEN,
                'Seu usuário não possui uma organização vinculada.'
            );
        }

        $this->tenantContext->set($organization);
        $this->permissionRegistrar->setPermissionsTeamId(
            $organization?->getKey()
        );

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        try {
            return $next($request);
        } finally {
            $this->clearContext();
        }
    }

    private function clearContext(): void
    {
        $this->tenantContext->clear();
        $this->permissionRegistrar->setPermissionsTeamId(null);
    }
}
