<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProcurementMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            WarehouseSeeder::class,
            ApplicationCenterSeeder::class,
            PaymentConditionSeeder::class,
        ]);
    }
}
