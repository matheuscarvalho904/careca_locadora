<?php

namespace Tests\Feature\Filament;

use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessPartnerModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_have_multiple_roles_and_related_records(): void
    {
        $organization = Organization::factory()->create();

        $partner = BusinessPartner::factory()
            ->for($organization)
            ->create([
                'roles' => ['customer', 'supplier', 'carrier'],
            ]);

        $partner->contacts()->create([
            'name' => 'Responsável Comercial',
            'email' => 'comercial@example.com',
            'is_primary' => true,
        ]);

        $partner->addresses()->create([
            'type' => 'main',
            'postal_code' => '78580000',
            'city' => 'Alta Floresta',
            'state' => 'MT',
            'is_primary' => true,
        ]);

        $this->assertSame(
            ['customer', 'supplier', 'carrier'],
            $partner->fresh()->roles
        );

        $this->assertCount(1, $partner->contacts);
        $this->assertCount(1, $partner->addresses);
        $this->assertStringStartsWith('PAR-', $partner->code);
    }

    public function test_user_cannot_manage_partner_from_another_organization(): void
    {
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();

        $user = User::factory()->for($organizationA)->create();
        $partner = BusinessPartner::factory()->for($organizationB)->create();

        $this->assertFalse($user->can('view', $partner));
        $this->assertFalse($user->can('update', $partner));
    }
}
