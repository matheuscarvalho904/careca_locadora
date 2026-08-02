<?php

namespace Tests\Feature\Fleet;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_receives_prefix_from_category_sequence(): void
    {
        $organization = Organization::factory()->create();

        $category = AssetCategory::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'name' => 'Veículo leve',
            'prefix' => 'VL',
            'asset_type' => 'vehicle',
            'meter_type' => 'odometer',
            'requires_plate' => true,
            'requires_renavam' => true,
            'requires_chassis' => true,
            'status' => 'active',
        ]);

        $first = Asset::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'category_id' => $category->id,
            'name' => 'Fiat Strada',
            'plate' => 'ABC1D23',
        ]);

        $second = Asset::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'category_id' => $category->id,
            'name' => 'Fiat Toro',
            'plate' => 'DEF4G56',
        ]);

        $this->assertSame('VL-01', $first->prefix);
        $this->assertSame('VL-02', $second->prefix);
    }

    public function test_asset_supports_documents_and_photos(): void
    {
        $organization = Organization::factory()->create();

        $category = AssetCategory::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'name' => 'Escavadeira',
            'prefix' => 'EH',
            'asset_type' => 'equipment',
            'meter_type' => 'hourmeter',
            'requires_plate' => false,
            'requires_renavam' => false,
            'requires_chassis' => true,
            'status' => 'active',
        ]);

        $asset = Asset::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'category_id' => $category->id,
            'name' => 'Komatsu PC200',
            'chassis' => 'CHASSIS-001',
        ]);

        $asset->documents()->create([
            'type' => 'insurance',
            'number' => 'SEG-001',
            'expires_at' => now()->addYear(),
        ]);

        $asset->photos()->create([
            'type' => 'front',
            'file_path' => 'fleet/photos/front.jpg',
            'is_featured' => true,
        ]);

        $this->assertCount(1, $asset->documents);
        $this->assertCount(1, $asset->photos);
    }
}
