<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\ProductBrand;
use Illuminate\Database\Seeder;

class ProductBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Sem marca', 'Original', 'Paralela', 'Importada', 'Diversos',
            '3M', 'ACDelco', 'Bosch', 'Castrol', 'Continental', 'Dayco',
            'Delphi', 'Denso', 'Donaldson', 'Eaton', 'Elring', 'Fram',
            'Gates', 'Goodyear', 'Havoline', 'Hella', 'Hengst', 'K&N',
            'Knorr-Bremse', 'Koyo', 'Mahle', 'Mann-Filter', 'Mobil',
            'Monroe', 'Motul', 'NGK', 'Petronas', 'Philips', 'Pirelli',
            'Ravenol', 'SKF', 'Shell', 'TRW', 'Texaco', 'Timken',
            'TotalEnergies', 'Valeo', 'Valvoline', 'VDO', 'Wega',
            'Wurth', 'ZF', 'Bridgestone', 'Firestone', 'Michelin',
            'Itaubá', 'Lubrax', 'Ipiranga', 'Petrobras', 'Bardahl',
        ];

        Organization::query()->each(function (Organization $organization) use ($brands): void {
            foreach ($brands as $brand) {
                ProductBrand::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'name' => $brand,
                    ],
                    [
                        'status' => 'active',
                    ],
                );
            }
        });
    }
}
