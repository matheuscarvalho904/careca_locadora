<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'category_id' => AssetCategory::factory(),
            'name' => fake()->randomElement([
                'Fiat Strada',
                'Toyota Hilux',
                'Volkswagen Saveiro',
            ]),
            'plate' => strtoupper(fake()->bothify('???#?##')),
            'brand' => fake()->randomElement(['FIAT', 'TOYOTA', 'VOLKSWAGEN']),
            'model' => fake()->word(),
            'manufacture_year' => fake()->numberBetween(2018, 2025),
            'model_year' => fake()->numberBetween(2019, 2026),
            'meter_type' => 'odometer',
            'operational_status' => 'available',
            'rental_status' => 'available',
            'status' => 'active',
        ];
    }
}
