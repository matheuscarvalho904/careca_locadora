<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\RentalContract;
use App\Models\RentalDelivery;
use App\Models\RentalReservation;
use App\Services\Rentals\RentalReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('conclui a reserva ao finalizar a devolução', function (): void {
    $organization = Organization::factory()->create();

    $partner = BusinessPartner::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'roles' => ['customer'],
            'person_type' => 'legal',
            'legal_name' => 'Cliente Fluxo Completo',
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
            'name' => 'Veículo fluxo completo',
            'operational_status' => 'available',
            'rental_status' => 'rented',
            'status' => 'active',
        ]);

    $reservation = RentalReservation::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'business_partner_id' => $partner->id,
            'status' => 'converted',
            'pickup_expected_at' => now()->subDays(5),
            'return_expected_at' => now(),
            'subtotal' => 1000,
            'total_value' => 1000,
        ]);

    $contract = RentalContract::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'reservation_id' => $reservation->id,
            'business_partner_id' => $partner->id,
            'status' => 'active',
            'starts_at' => now()->subDays(5),
            'ends_at' => now(),
            'subtotal' => 1000,
            'total_value' => 1000,
        ]);

    $contractItem = $contract->items()->create([
        'organization_id' => $organization->id,
        'asset_id' => $asset->id,
        'starts_at' => $contract->starts_at,
        'ends_at' => $contract->ends_at,
        'billing_unit' => 'daily',
        'quantity' => 5,
        'unit_value' => 200,
        'total_value' => 1000,
        'initial_odometer' => 10000,
    ]);

    $delivery = RentalDelivery::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'contract_id' => $contract->id,
            'status' => 'completed',
            'scheduled_at' => $contract->starts_at,
            'delivered_at' => $contract->starts_at,
            'customer_signer_name' => 'Recebedor',
        ]);

    $delivery->items()->create([
        'organization_id' => $organization->id,
        'contract_item_id' => $contractItem->id,
        'asset_id' => $asset->id,
        'odometer' => 10000,
        'fuel_level' => 'full',
    ]);

    $service = app(RentalReturnService::class);
    $return = $service->createFromContract($contract->fresh());

    $return->update([
        'customer_signer_name' => 'Responsável devolução',
    ]);

    $return->items()->firstOrFail()->update([
        'final_odometer' => 11000,
        'final_fuel_level' => 'full',
    ]);

    $service->complete($return->fresh());

    expect($reservation->fresh()->status)->toBe('completed')
        ->and($contract->fresh()->status)->toBe('closed')
        ->and($asset->fresh()->rental_status)->toBe('available');
});
