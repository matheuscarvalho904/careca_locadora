<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('permite administrar organizações somente ao administrador da plataforma', function (): void {
    $organization = Organization::factory()->create();

    $admin = User::factory()
        ->for($organization)
        ->platformAdmin()
        ->create();

    $user = User::factory()
        ->for($organization)
        ->create([
            'is_platform_admin' => false,
        ]);

    expect($admin->can('viewAny', Organization::class))->toBeTrue()
        ->and($user->can('viewAny', Organization::class))->toBeFalse();
});
