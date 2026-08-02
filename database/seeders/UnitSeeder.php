<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Unidade', 'symbol' => 'UN'],
            ['name' => 'Peça', 'symbol' => 'PC'],
            ['name' => 'Kit', 'symbol' => 'KIT'],
            ['name' => 'Caixa', 'symbol' => 'CX'],
            ['name' => 'Pacote', 'symbol' => 'PCT'],
            ['name' => 'Par', 'symbol' => 'PAR'],
            ['name' => 'Jogo', 'symbol' => 'JG'],
            ['name' => 'Rolo', 'symbol' => 'RL'],
            ['name' => 'Tubo', 'symbol' => 'TB'],
            ['name' => 'Galão', 'symbol' => 'GL'],
            ['name' => 'Quilograma', 'symbol' => 'KG'],
            ['name' => 'Grama', 'symbol' => 'G'],
            ['name' => 'Tonelada', 'symbol' => 'T'],
            ['name' => 'Litro', 'symbol' => 'L'],
            ['name' => 'Mililitro', 'symbol' => 'ML'],
            ['name' => 'Metro', 'symbol' => 'M'],
            ['name' => 'Centímetro', 'symbol' => 'CM'],
            ['name' => 'Milímetro', 'symbol' => 'MM'],
            ['name' => 'Metro quadrado', 'symbol' => 'M²'],
            ['name' => 'Metro cúbico', 'symbol' => 'M³'],
            ['name' => 'Hora', 'symbol' => 'H'],
            ['name' => 'Dia', 'symbol' => 'DIA'],
        ];

        Organization::query()->each(function (Organization $organization) use ($units): void {
            foreach ($units as $unit) {
                Unit::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'symbol' => $unit['symbol'],
                    ],
                    [
                        'name' => $unit['name'],
                        'status' => 'active',
                    ],
                );
            }
        });
    }
}
