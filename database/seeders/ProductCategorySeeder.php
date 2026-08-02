<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'PECAS', 'name' => 'Peças e acessórios'],
            ['code' => 'COMB', 'name' => 'Combustíveis'],
            ['code' => 'LUBR', 'name' => 'Lubrificantes e fluidos'],
            ['code' => 'PNEUS', 'name' => 'Pneus e câmaras'],
            ['code' => 'FILTROS', 'name' => 'Filtros'],
            ['code' => 'BATERIAS', 'name' => 'Baterias'],
            ['code' => 'CORREIAS', 'name' => 'Correias e tensionadores'],
            ['code' => 'FERRAMENTAS', 'name' => 'Ferramentas'],
            ['code' => 'EPI', 'name' => 'EPI'],
            ['code' => 'ELETRICA', 'name' => 'Material elétrico'],
            ['code' => 'HIDRAULICA', 'name' => 'Material hidráulico'],
            ['code' => 'PREDIAL', 'name' => 'Material predial'],
            ['code' => 'ESCRITORIO', 'name' => 'Material de escritório'],
            ['code' => 'LIMPEZA', 'name' => 'Material de limpeza'],
            ['code' => 'ACESSORIOS', 'name' => 'Acessórios veiculares'],
            ['code' => 'DIVERSOS', 'name' => 'Diversos'],
        ];

        Organization::query()->each(function (Organization $organization) use ($categories): void {
            foreach ($categories as $category) {
                ProductCategory::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'code' => $category['code'],
                    ],
                    [
                        'name' => $category['name'],
                        'status' => 'active',
                    ],
                );
            }
        });
    }
}
