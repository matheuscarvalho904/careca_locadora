<?php

namespace Tests\Feature\Filament;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizationBusinessFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_business_fields_are_available_and_cast_correctly(): void
    {
        $this->assertTrue(Schema::hasColumns('organizations', [
            'trade_name',
            'person_type',
            'postal_code',
            'address',
            'city',
            'state',
            'primary_color',
            'business_segment',
            'tags',
            'external_data',
        ]));

        $organization = Organization::factory()->create([
            'trade_name' => 'Careca Locadora',
            'tags' => ['locação', 'veículos'],
            'external_data' => ['source' => 'manual'],
        ]);

        $this->assertSame(['locação', 'veículos'], $organization->tags);
        $this->assertSame(['source' => 'manual'], $organization->external_data);
    }
}
