<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetCategory>
 */
class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->unique()->words(2, true),
            'prefix' => strtoupper(fake()->unique()->lexify('??')),
            'asset_type' => 'vehicle',
            'meter_type' => 'odometer',
            'requires_plate' => true,
            'requires_renavam' => true,
            'requires_chassis' => true,
            'status' => 'active',
        ];
    }
}
