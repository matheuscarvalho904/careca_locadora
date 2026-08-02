<?php

use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\RentalReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('salva reserva com valores monetários padrão mesmo quando o formulário envia null', function (): void {
    $organization = Organization::factory()->create();

    $partner = BusinessPartner::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'roles' => ['customer'],
            'person_type' => 'legal',
            'legal_name' => 'Cliente Teste',
            'status' => 'active',
        ]);

    $reservation = RentalReservation::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'business_partner_id' => $partner->id,
            'pickup_expected_at' => now()->addDay(),
            'return_expected_at' => now()->addDays(3),
            'subtotal' => null,
            'discount_value' => null,
            'additional_value' => null,
            'deposit_value' => null,
            'total_value' => null,
        ]);

    expect((float) $reservation->subtotal)->toBe(0.0)
        ->and((float) $reservation->discount_value)->toBe(0.0)
        ->and((float) $reservation->additional_value)->toBe(0.0)
        ->and((float) $reservation->deposit_value)->toBe(0.0)
        ->and((float) $reservation->total_value)->toBe(0.0);
});
