<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BusinessPartner;
use App\Models\Organization;
use App\Models\RentalContract;
use App\Models\RentalReturn;
use App\Services\Rentals\RentalInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('gera fatura de locação e contas a receber a partir do contrato encerrado', function (): void {
    $organization = Organization::factory()->create();

    $partner = BusinessPartner::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'roles' => ['customer'],
            'person_type' => 'legal',
            'legal_name' => 'Cliente Fatura',
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
            'name' => 'Veículo faturado',
            'operational_status' => 'available',
            'rental_status' => 'available',
            'status' => 'active',
        ]);

    $contract = RentalContract::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'business_partner_id' => $partner->id,
            'status' => 'closed',
            'starts_at' => now()->subDays(5),
            'ends_at' => now(),
            'closed_at' => now(),
            'subtotal' => 1000,
            'total_value' => 1000,
        ]);

    $contract->items()->create([
        'organization_id' => $organization->id,
        'asset_id' => $asset->id,
        'starts_at' => $contract->starts_at,
        'ends_at' => $contract->ends_at,
        'billing_unit' => 'daily',
        'quantity' => 5,
        'unit_value' => 200,
        'total_value' => 1000,
    ]);

    RentalReturn::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'contract_id' => $contract->id,
            'delivery_id' => \App\Models\RentalDelivery::query()
                ->withoutOrganizationScope()
                ->create([
                    'organization_id' => $organization->id,
                    'contract_id' => $contract->id,
                    'status' => 'completed',
                    'customer_signer_name' => 'Recebedor',
                ])->id,
            'status' => 'completed',
            'fuel_value' => 200,
            'damage_value' => 300,
            'cleaning_value' => 100,
            'total_charge_value' => 600,
            'customer_signer_name' => 'Devolvedor',
        ]);

    $service = app(RentalInvoiceService::class);
    $invoice = $service->createFromContract($contract->fresh());

    expect($invoice->items)->toHaveCount(4)
        ->and((float) $invoice->total_value)->toBe(1600.0);

    $issued = $service->issue($invoice, 2);

    expect($issued->status)->toBe('issued')
        ->and($issued->receivables)->toHaveCount(2)
        ->and((float) $issued->receivables->sum('original_value'))->toBe(1600.0);
});
