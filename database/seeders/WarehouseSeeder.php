<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            ['code' => 'ALM-CENTRAL', 'name' => 'Almoxarifado Central'],
            ['code' => 'ALM-OFICINA', 'name' => 'Almoxarifado da Oficina'],
            ['code' => 'ALM-PATIO', 'name' => 'Almoxarifado do Pátio'],
            ['code' => 'ALM-COMB', 'name' => 'Estoque de Combustíveis'],
        ];

        Organization::query()->each(function (Organization $organization) use ($warehouses): void {
            foreach ($warehouses as $warehouse) {
                Warehouse::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'code' => $warehouse['code'],
                    ],
                    [
                        'name' => $warehouse['name'],
                        'status' => 'active',
                    ],
                );
            }
        });
    }
}
