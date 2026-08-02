<?php

namespace Database\Seeders;

use App\Models\BodyType;
use App\Models\FleetBrand;
use App\Models\FuelType;
use App\Models\Organization;
use App\Models\TractionType;
use App\Models\TransmissionType;
use App\Models\VehicleColor;
use Illuminate\Database\Seeder;

class FleetMasterCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();

        if ($organization === null) {
            return;
        }

        foreach ([
            'FIAT',
            'VOLKSWAGEN',
            'CHEVROLET',
            'TOYOTA',
            'FORD',
            'RENAULT',
            'HYUNDAI',
            'MERCEDES-BENZ',
            'SCANIA',
            'VOLVO',
            'KOMATSU',
            'CATERPILLAR',
            'JCB',
        ] as $name) {
            FleetBrand::query()->withoutOrganizationScope()->updateOrCreate(
                ['organization_id' => $organization->id, 'name' => $name],
                ['status' => 'active'],
            );
        }

        $this->seedSimple(FuelType::class, $organization->id, [
            'Gasolina', 'Etanol', 'Flex', 'Diesel S10', 'Diesel S500',
            'Elétrico', 'Híbrido', 'GNV',
        ]);

        $this->seedSimple(TransmissionType::class, $organization->id, [
            'Manual', 'Automático', 'CVT', 'Automatizado',
        ]);

        $this->seedSimple(TractionType::class, $organization->id, [
            '4x2', '4x4', '6x2', '6x4', '8x2', '8x4',
        ]);

        $this->seedSimple(BodyType::class, $organization->id, [
            'Hatch', 'Sedã', 'SUV', 'Picape', 'Van', 'Furgão',
            'Basculante', 'Pipa', 'Prancha', 'Carreta', 'Implemento',
        ]);

        $this->seedSimple(VehicleColor::class, $organization->id, [
            'Branco', 'Preto', 'Prata', 'Cinza', 'Vermelho',
            'Azul', 'Verde', 'Amarelo', 'Marrom', 'Laranja',
        ]);
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @param array<int, string> $items
     */
    private function seedSimple(string $modelClass, string $organizationId, array $items): void
    {
        foreach ($items as $order => $name) {
            $modelClass::query()->withoutOrganizationScope()->updateOrCreate(
                ['organization_id' => $organizationId, 'name' => $name],
                [
                    'display_order' => $order + 1,
                    'status' => 'active',
                ],
            );
        }
    }
}
