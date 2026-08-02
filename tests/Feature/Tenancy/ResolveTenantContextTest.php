<?php

use App\Contracts\TenantContext;
use App\Http\Middleware\ResolveTenantContext;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

it('define e limpa o contexto da organização do usuário', function (): void {
    $organization = new Organization();
    $organization->id = (string) str()->uuid();

    $user = new User();
    $user->id = (string) str()->uuid();
    $user->organization_id = $organization->id;
    $user->is_platform_admin = false;
    $user->setRelation('organization', $organization);

    $request = Request::create('/app', 'GET');
    $request->setUserResolver(fn (): User => $user);

    $response = app(ResolveTenantContext::class)->handle(
        $request,
        function () use ($organization): Response {
            expect(app(TenantContext::class)->id())
                ->toBe($organization->id);

            expect(app(PermissionRegistrar::class)->getPermissionsTeamId())
                ->toBe($organization->id);

            return new Response('OK');
        }
    );

    expect($response->getContent())->toBe('OK');
    expect(app(TenantContext::class)->id())->toBeNull();
    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBeNull();
});

it('nega acesso ao usuário comum sem organização', function (): void {
    $user = new User();
    $user->id = (string) str()->uuid();
    $user->organization_id = null;
    $user->is_platform_admin = false;
    $user->setRelation('organization', null);

    $request = Request::create('/app', 'GET');
    $request->setUserResolver(fn (): User => $user);

    app(ResolveTenantContext::class)->handle(
        $request,
        fn (): Response => new Response('NÃO DEVERIA EXECUTAR')
    );
})->throws(
    Symfony\Component\HttpKernel\Exception\HttpException::class,
    'Seu usuário não possui uma organização vinculada.'
);
