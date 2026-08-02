<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\RentalReservation;
use App\Services\Rentals\ReservationToContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('converte reserva em contrato preservando cliente ativos período e valores', function (): void {
    $organization = Organization::factory()->create();

    $partner = BusinessPartner::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'roles' => ['customer'],
            'person_type' => 'legal',
            'legal_name' => 'Cliente Contrato',
            'status' => 'active',
        ]);

    $category = AssetCategory::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'name' => 'Veículos leves',
            'prefix' => 'VL',
            'requires_plate' => true,
            'requires_renavam' => true,
            'requires_chassis' => true,
            'display_order' => 1,
            'status' => 'active',
        ]);

    $asset = Asset::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'category_id' => $category->id,
            'name' => 'Veículo teste',
            'operational_status' => 'available',
            'rental_status' => 'available',
            'status' => 'active',
        ]);

    $reservation = RentalReservation::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'business_partner_id' => $partner->id,
            'status' => 'confirmed',
            'pickup_expected_at' => now()->addDay(),
            'return_expected_at' => now()->addDays(5),
            'subtotal' => 2500,
            'discount_value' => 100,
            'additional_value' => 50,
            'deposit_value' => 500,
        ]);

    $reservation->items()->create([
        'organization_id' => $organization->id,
        'asset_id' => $asset->id,
        'starts_at' => $reservation->pickup_expected_at,
        'ends_at' => $reservation->return_expected_at,
        'billing_unit' => 'daily',
        'quantity' => 5,
        'unit_value' => 500,
        'total_value' => 2500,
    ]);

    $contract = app(ReservationToContractService::class)
        ->convert($reservation->fresh());

    expect($contract->reservation_id)->toBe($reservation->id)
        ->and($contract->business_partner_id)->toBe($partner->id)
        ->and($contract->items)->toHaveCount(1)
        ->and((float) $contract->total_value)->toBe(2450.0)
        ->and($reservation->fresh()->status)->toBe('converted')
        ->and($asset->fresh()->rental_status)->toBe('rented');
});
