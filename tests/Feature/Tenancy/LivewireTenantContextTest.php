<?php

use App\Contracts\TenantContext;
use App\Http\Middleware\ResolveTenantContext;
use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('registra o resolvedor na lista de middlewares persistentes', function (): void {
    expect(Livewire::getPersistentMiddleware())
        ->toContain(ResolveTenantContext::class);
});

it('aplica o resolvedor diretamente na rota global de update do Livewire', function (): void {
    /** @var LaravelRoute|null $route */
    $route = collect(Route::getRoutes()->getRoutes())
        ->first(
            fn (LaravelRoute $route): bool =>
                in_array('POST', $route->methods(), true)
                && str_ends_with($route->uri(), '/update')
                && str_contains($route->uri(), 'livewire')
        );

    expect($route)->not->toBeNull();

    expect($route->gatherMiddleware())
        ->toContain('web')
        ->toContain(ResolveTenantContext::class);
});

it('define o tenant durante a requisição e permite criar model organizacional', function (): void {
    $organization = Organization::factory()->create();

    $user = User::factory()->create([
        'organization_id' => $organization->id,
        'is_platform_admin' => false,
        'status' => 'active',
    ]);

    $user->setRelation('organization', $organization);

    $request = Request::create('/livewire/update', 'POST');
    $request->setUserResolver(fn (): User => $user);

    $partner = null;

    $response = app(ResolveTenantContext::class)->handle(
        $request,
        function () use (&$partner, $organization): Response {
            expect(app(TenantContext::class)->id())
                ->toBe($organization->id);

            expect(app(PermissionRegistrar::class)->getPermissionsTeamId())
                ->toBe($organization->id);

            $partner = BusinessPartner::query()->create([
                'roles' => ['customer'],
                'person_type' => 'legal',
                'legal_name' => 'Cliente Livewire',
                'status' => 'active',
            ]);

            return new Response('OK');
        }
    );

    expect($response->getContent())->toBe('OK');
    expect($partner)->not->toBeNull();
    expect($partner->organization_id)->toBe($organization->id);
    expect(app(TenantContext::class)->id())->toBeNull();
    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBeNull();
});

it('limpa qualquer contexto residual em requisição sem usuário', function (): void {
    $organization = Organization::factory()->create();

    app(TenantContext::class)->set($organization);
    app(PermissionRegistrar::class)->setPermissionsTeamId($organization->id);

    $request = Request::create('/livewire/update', 'POST');
    $request->setUserResolver(fn () => null);

    app(ResolveTenantContext::class)->handle(
        $request,
        fn (): Response => new Response('OK')
    );

    expect(app(TenantContext::class)->id())->toBeNull();
    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBeNull();
});
