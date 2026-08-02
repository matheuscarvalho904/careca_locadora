<?php

namespace Database\Seeders;

use App\Models\ApplicationCenter;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class ApplicationCenterSeeder extends Seeder
{
    public function run(): void
    {
        $centers = [
            ['code' => 'ADMIN', 'name' => 'Administrativo', 'type' => 'administrative'],
            ['code' => 'PREDIAL', 'name' => 'Despesa Predial', 'type' => 'building'],
            ['code' => 'OFICINA', 'name' => 'Oficina', 'type' => 'workshop'],
            ['code' => 'PATIO', 'name' => 'Pátio', 'type' => 'yard'],
            ['code' => 'ESCRITORIO', 'name' => 'Escritório', 'type' => 'office'],
            ['code' => 'FROTA', 'name' => 'Frota', 'type' => 'general'],
            ['code' => 'LIMPEZA', 'name' => 'Limpeza', 'type' => 'general'],
            ['code' => 'TI', 'name' => 'Tecnologia da Informação', 'type' => 'general'],
        ];

        Organization::query()->each(function (Organization $organization) use ($centers): void {
            foreach ($centers as $center) {
                ApplicationCenter::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'code' => $center['code'],
                    ],
                    [
                        'name' => $center['name'],
                        'type' => $center['type'],
                        'status' => 'active',
                    ],
                );
            }
        });
    }
}
