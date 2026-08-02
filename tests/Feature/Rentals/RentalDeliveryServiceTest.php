<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\RentalContract;
use App\Services\Rentals\RentalDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cria e conclui entrega a partir de contrato ativo', function (): void {
    $organization = Organization::factory()->create();

    $partner = BusinessPartner::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'roles' => ['customer'],
            'person_type' => 'legal',
            'legal_name' => 'Cliente Entrega',
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
            'name' => 'Veículo de entrega',
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
            'starts_at' => now(),
            'ends_at' => now()->addDays(5),
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
    ]);

    $service = app(RentalDeliveryService::class);
    $delivery = $service->createFromContract($contract);

    expect($delivery->items)->toHaveCount(1)
        ->and($delivery->contract_id)->toBe($contract->id)
        ->and($delivery->status)->toBe('draft');

    $delivery->update([
        'customer_signer_name' => 'Recebedor Teste',
    ]);

    $delivery->items()->firstOrFail()->update([
        'odometer' => 12345,
        'fuel_level' => 'full',
    ]);

    $completed = $service->complete($delivery->fresh());

    expect($completed->status)->toBe('completed')
        ->and($completed->delivered_at)->not->toBeNull()
        ->and((float) $contractItem->fresh()->initial_odometer)->toBe(12345.0)
        ->and($asset->fresh()->rental_status)->toBe('rented');
});
