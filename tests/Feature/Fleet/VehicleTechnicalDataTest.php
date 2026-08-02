<?php

namespace Tests\Feature\Fleet;

use App\Data\Fleet\VehicleLookupResult;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTechnicalDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_result_exposes_complete_asset_data(): void
    {
        $result = new VehicleLookupResult(
            plate: 'GAN9D77',
            oldPlate: 'GAN9377',
            brand: 'FIAT',
            model: 'UNO VIVACE 1.0',
            manufactureYear: 2015,
            modelYear: 2016,
            fuelType: 'Flex',
            seats: 5,
            engineDescription: '1.0',
            engineDisplacementCc: 1000,
            enginePowerHp: 75,
            axles: 2,
            grossWeightT: 1.2,
            maximumTractionCapacityT: 2.0,
            city: 'São José do Rio Preto',
            state: 'SP',
            species: 'Passageiro',
            origin: 'Nacional',
            segment: 'Auto',
            subsegment: 'AU - ENTRADA',
            fipeCode: '001303-0',
            fipeDescription: 'UNO VIVACE/RUA 1.0 EVO Fire Flex 8V 5p',
            fipeValue: 33174.00,
            fipeReferenceMonth: 'julho de 2026',
            fipeScore: 95,
        );

        $data = $result->toAssetData();

        $this->assertSame('GAN9377', $data['old_plate']);
        $this->assertSame(1000, $data['engine_displacement_cc']);
        $this->assertSame(75, $data['engine_power_hp']);
        $this->assertSame('SP', $data['registration_state']);
        $this->assertSame(33174.00, $data['fipe_value']);
        $this->assertSame(
            'FIAT UNO VIVACE 1.0 Flex 2016',
            $result->suggestedName()
        );
    }

    public function test_asset_casts_technical_values(): void
    {
        $organization = Organization::factory()->create();
        $category = AssetCategory::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $asset = Asset::query()->withoutOrganizationScope()->create([
            'organization_id' => $organization->id,
            'category_id' => $category->id,
            'name' => 'FIAT UNO VIVACE 1.0 Flex 2016',
            'plate' => 'GAN9D77',
            'engine_displacement_cc' => 1000,
            'engine_power_hp' => 75,
            'gross_weight_t' => 1.2,
            'fipe_value' => 33174.00,
            'status' => 'active',
            'operational_status' => 'available',
            'rental_status' => 'available',
            'meter_type' => 'odometer',
        ]);

        $this->assertSame(1000, $asset->engine_displacement_cc);
        $this->assertSame(75, $asset->engine_power_hp);
        $this->assertSame('1.200', $asset->gross_weight_t);
        $this->assertSame('33174.00', $asset->fipe_value);
    }
}
