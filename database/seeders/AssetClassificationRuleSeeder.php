<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetClassificationRule;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class AssetClassificationRuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Organization::query()->get() as $organization) {
            $this->createRule(
                $organization->id,
                ['VEÍCULO LEVE', 'VEICULO LEVE'],
                'Veículo leve por tipo',
                vehicleType: 'AUTOMOVEL',
                keywords: ['STRADA', 'UNO', 'SAVEIRO', 'HILUX', 'DUSTER'],
                meterType: 'odometer',
                autoApply: true,
                priority: 10,
            );

            $this->createRule(
                $organization->id,
                ['CAMINHÃO BASCULANTE', 'CAMINHAO BASCULANTE'],
                'Caminhão basculante por carroceria',
                bodyType: 'BASCULANTE',
                keywords: ['BASCULANTE'],
                meterType: 'odometer',
                autoApply: true,
                priority: 10,
            );

            $this->createRule(
                $organization->id,
                ['ESCAVADEIRA HIDRÁULICA', 'ESCAVADEIRA HIDRAULICA', 'ESCAVADEIRA'],
                'Escavadeira por modelo',
                keywords: ['PC210', 'PC200', '320D', '320', 'EC210', 'CX220'],
                meterType: 'hourmeter',
                autoApply: true,
                priority: 20,
            );

            $this->createRule(
                $organization->id,
                ['MOTONIVELADORA'],
                'Motoniveladora por modelo',
                keywords: ['120K', '120H', '140K', 'RG140', '845B'],
                meterType: 'hourmeter',
                autoApply: true,
                priority: 20,
            );

            $this->createRule(
                $organization->id,
                ['RETROESCAVADEIRA'],
                'Retroescavadeira por modelo',
                keywords: ['3CX', '580N', '580M', 'B95B'],
                meterType: 'hourmeter',
                autoApply: true,
                priority: 20,
            );

            $this->createRule(
                $organization->id,
                ['CARRETA', 'SEMIRREBOQUE', 'SEMI-REBOQUE'],
                'Implemento rodoviário',
                vehicleType: 'REBOQUE',
                keywords: ['RANDON', 'FACCHINI', 'LIBRELATO', 'GUERRA'],
                meterType: 'odometer',
                autoApply: false,
                priority: 30,
            );
        }
    }

    /**
     * @param array<int, string> $categoryNames
     * @param array<int, string> $keywords
     */
    private function createRule(
        string $organizationId,
        array $categoryNames,
        string $name,
        ?string $brand = null,
        ?string $model = null,
        ?string $vehicleType = null,
        ?string $bodyType = null,
        ?string $segment = null,
        ?string $subsegment = null,
        array $keywords = [],
        string $meterType = 'odometer',
        bool $autoApply = false,
        int $priority = 100,
    ): void {
        $category = AssetCategory::query()
            ->withoutOrganizationScope()
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($categoryNames): void {
                foreach ($categoryNames as $categoryName) {
                    $query->orWhereRaw('UPPER(name) = ?', [mb_strtoupper($categoryName)]);
                }
            })
            ->first();

        if ($category === null) {
            return;
        }

        AssetClassificationRule::query()
            ->withoutOrganizationScope()
            ->updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'name' => $name,
                ],
                [
                    'category_id' => $category->id,
                    'brand_pattern' => $brand,
                    'model_pattern' => $model,
                    'vehicle_type_pattern' => $vehicleType,
                    'body_type_pattern' => $bodyType,
                    'segment_pattern' => $segment,
                    'subsegment_pattern' => $subsegment,
                    'keywords' => $keywords,
                    'meter_type' => $meterType,
                    'priority' => $priority,
                    'minimum_confidence' => 70,
                    'auto_apply' => $autoApply,
                    'status' => 'active',
                ],
            );
    }
}
