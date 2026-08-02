<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\RentalContract;
use App\Models\RentalDelivery;
use App\Services\Rentals\RentalReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cria e conclui devolução fechando contrato e liberando ativo', function (): void {
    $organization = Organization::factory()->create();

    $partner = BusinessPartner::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'roles' => ['customer'],
            'person_type' => 'legal',
            'legal_name' => 'Cliente Devolução',
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
            'name' => 'Veículo devolução',
            'operational_status' => 'available',
            'rental_status' => 'rented',
            'status' => 'active',
        ]);

    $contract = RentalContract::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
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

    $deliveryItem = $delivery->items()->create([
        'organization_id' => $organization->id,
        'contract_item_id' => $contractItem->id,
        'asset_id' => $asset->id,
        'odometer' => 10000,
        'fuel_level' => 'full',
    ]);

    $service = app(RentalReturnService::class);
    $return = $service->createFromContract($contract->fresh());

    expect($return->status)->toBe('draft')
        ->and($return->items)->toHaveCount(1);

    $return->update([
        'customer_signer_name' => 'Responsável devolução',
    ]);

    $item = $return->items()->firstOrFail();
    $item->update([
        'final_odometer' => 11250,
        'final_fuel_level' => 'half',
        'fuel_value' => 150,
        'cleaning_value' => 80,
    ]);

    $completed = $service->complete($return->fresh());

    expect($completed->status)->toBe('completed')
        ->and($completed->returned_at)->not->toBeNull()
        ->and((float) $completed->total_charge_value)->toBe(230.0)
        ->and((float) $item->fresh()->distance_used)->toBe(1250.0)
        ->and((float) $contractItem->fresh()->final_odometer)->toBe(11250.0)
        ->and($contract->fresh()->status)->toBe('closed')
        ->and($asset->fresh()->rental_status)->toBe('available');
});
