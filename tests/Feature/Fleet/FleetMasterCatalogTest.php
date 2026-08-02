<?php

namespace Tests\Feature\Fleet;

use App\Models\FleetBrand;
use App\Models\FleetModel;
use App\Models\FleetVersion;
use App\Models\FuelType;
use App\Models\Organization;
use App\Models\TransmissionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetMasterCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_model_and_version_are_linked(): void
    {
        $organization = Organization::factory()->create();

        $brand = FleetBrand::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'name' => 'FIAT',
            'status' => 'active',
        ]);

        $model = FleetModel::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'brand_id' => $brand->id,
            'name' => 'STRADA',
            'status' => 'active',
        ]);

        $version = FleetVersion::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'model_id' => $model->id,
            'name' => 'FREEDOM 1.3',
            'fipe_code' => '001234-5',
            'status' => 'active',
        ]);

        $this->assertSame('FIAT', $model->brand->name);
        $this->assertSame('STRADA', $version->model->name);
        $this->assertSame('001234-5', $version->fipe_code);
    }

    public function test_simple_catalogs_are_isolated_by_organization(): void
    {
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();

        FuelType::query()->withoutOrganizationScope()->create([
            'organization_id' => $organizationA->id,
            'name' => 'Diesel S10',
            'status' => 'active',
        ]);

        TransmissionType::query()->withoutOrganizationScope()->create([
            'organization_id' => $organizationB->id,
            'name' => 'Automático',
            'status' => 'active',
        ]);

        $this->assertSame(
            1,
            FuelType::query()->withoutOrganizationScope()
                ->where('organization_id', $organizationA->id)
                ->count()
        );

        $this->assertSame(
            0,
            FuelType::query()->withoutOrganizationScope()
                ->where('organization_id', $organizationB->id)
                ->count()
        );
    }
}
