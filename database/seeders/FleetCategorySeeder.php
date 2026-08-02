<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class FleetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();

        if ($organization === null) {
            return;
        }

        $categories = [
            ['name' => 'Veículo leve', 'prefix' => 'VL', 'asset_type' => 'vehicle', 'meter_type' => 'odometer', 'requires_plate' => true],
            ['name' => 'Caminhão basculante', 'prefix' => 'CB', 'asset_type' => 'vehicle', 'meter_type' => 'odometer', 'requires_plate' => true],
            ['name' => 'Reboque', 'prefix' => 'RE', 'asset_type' => 'implement', 'meter_type' => 'odometer', 'requires_plate' => true],
            ['name' => 'Pá carregadeira', 'prefix' => 'PC', 'asset_type' => 'equipment', 'meter_type' => 'hourmeter', 'requires_plate' => false],
            ['name' => 'Escavadeira hidráulica', 'prefix' => 'EH', 'asset_type' => 'equipment', 'meter_type' => 'hourmeter', 'requires_plate' => false],
            ['name' => 'Caminhonete', 'prefix' => 'CA', 'asset_type' => 'vehicle', 'meter_type' => 'odometer', 'requires_plate' => true],
            ['name' => 'Motoniveladora', 'prefix' => 'MN', 'asset_type' => 'equipment', 'meter_type' => 'hourmeter', 'requires_plate' => false],
        ];

        foreach ($categories as $index => $category) {
            AssetCategory::query()->withoutOrganizationScope()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'prefix' => $category['prefix'],
                ],
                array_merge($category, [
                    'requires_renavam' => $category['requires_plate'],
                    'requires_chassis' => true,
                    'display_order' => $index + 1,
                    'status' => 'active',
                ]),
            );
        }
    }
}
