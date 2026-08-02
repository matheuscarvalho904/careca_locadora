<?php

namespace Tests\Feature\Fleet;

use App\Models\AssetCategory;
use App\Models\AssetClassificationRule;
use App\Models\Organization;
use App\Services\Fleet\AssetClassificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetClassificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_classifies_a_vehicle_by_brand_model_and_keywords(): void
    {
        $organization = Organization::factory()->create();

        $category = AssetCategory::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Veículo leve',
        ]);

        AssetClassificationRule::query()
            ->withoutOrganizationScope()
            ->create([
                'organization_id' => $organization->id,
                'category_id' => $category->id,
                'name' => 'Fiat Strada',
                'brand_pattern' => 'FIAT',
                'model_pattern' => 'STRADA',
                'keywords' => ['PICAPE'],
                'meter_type' => 'odometer',
                'priority' => 10,
                'minimum_confidence' => 60,
                'auto_apply' => true,
                'status' => 'active',
            ]);

        $result = app(AssetClassificationService::class)->classify(
            organizationId: $organization->id,
            assetData: [
                'plate' => 'ABC1D23',
                'brand' => 'FIAT',
                'model' => 'STRADA FREEDOM',
                'external_data' => [
                    'vehicle_lookup' => [
                        'body_type' => 'PICAPE',
                    ],
                ],
            ],
        );

        $this->assertTrue($result->matched());
        $this->assertSame($category->id, $result->categoryId);
        $this->assertSame('odometer', $result->meterType);
        $this->assertSame(65, $result->confidence);
        $this->assertFalse($result->canApplyAutomatically());
    }

    public function test_it_does_not_match_below_minimum_confidence(): void
    {
        $organization = Organization::factory()->create();

        $category = AssetCategory::factory()->create([
            'organization_id' => $organization->id,
        ]);

        AssetClassificationRule::query()
            ->withoutOrganizationScope()
            ->create([
                'organization_id' => $organization->id,
                'category_id' => $category->id,
                'name' => 'Escavadeira',
                'model_pattern' => 'PC210',
                'meter_type' => 'hourmeter',
                'priority' => 10,
                'minimum_confidence' => 50,
                'auto_apply' => true,
                'status' => 'active',
            ]);

        $result = app(AssetClassificationService::class)->classify(
            organizationId: $organization->id,
            assetData: [
                'brand' => 'KOMATSU',
                'model' => 'PC200',
            ],
        );

        $this->assertFalse($result->matched());
        $this->assertSame(0, $result->confidence);
    }
}
