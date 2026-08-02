<?php

namespace Tests\Feature\BusinessPartners;

use App\Models\BusinessPartner;
use App\Models\BusinessPartnerContact;
use App\Models\BusinessPartnerDocument;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessPartnerPremiumTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_supports_credit_and_authorized_contacts(): void
    {
        $organization = Organization::factory()->create();

        $partner = BusinessPartner::query()
            ->withoutOrganizationScope()
            ->create([
                'organization_id' => $organization->id,
                'roles' => ['customer'],
                'person_type' => 'legal',
                'legal_name' => 'Cliente Teste Ltda',
                'credit_limit' => 50000,
                'risk_level' => 'low',
                'credit_blocked' => false,
                'status' => 'active',
            ]);

        BusinessPartnerContact::query()->create([
            'business_partner_id' => $partner->id,
            'name' => 'Responsável',
            'can_withdraw_assets' => true,
            'can_return_assets' => true,
            'can_sign_contracts' => true,
        ]);

        $this->assertTrue($partner->isCustomer());
        $this->assertFalse($partner->isCreditBlocked());
        $this->assertCount(1, $partner->authorizedContacts()->get());
    }

    public function test_partner_supports_documents(): void
    {
        $organization = Organization::factory()->create();

        $partner = BusinessPartner::query()
            ->withoutOrganizationScope()
            ->create([
                'organization_id' => $organization->id,
                'roles' => ['customer'],
                'person_type' => 'individual',
                'legal_name' => 'Cliente Pessoa Física',
                'status' => 'active',
            ]);

        BusinessPartnerDocument::query()->create([
            'business_partner_id' => $partner->id,
            'type' => 'cnh',
            'title' => 'CNH',
            'file_path' => 'partners/documents/cnh.pdf',
        ]);

        $this->assertCount(1, $partner->documents()->get());
    }
}
