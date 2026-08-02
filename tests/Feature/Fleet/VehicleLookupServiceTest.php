<?php

namespace Tests\Feature\Fleet;

use App\Models\Organization;
use App\Models\VehicleLookupLog;
use App\Services\Fleet\VehicleLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class VehicleLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_is_logged_and_returns_asset_data(): void
    {
        Config::set('fleet.vehicle_lookup.provider', 'fake');

        $organization = Organization::factory()->create();

        $result = app(VehicleLookupService::class)->lookup(
            organizationId: $organization->id,
            plate: 'ABC1D23',
        );

        $this->assertSame('ABC1D23', $result->plate);
        $this->assertSame('FIAT', $result->brand);
        $this->assertSame('STRADA', $result->model);

        $log = VehicleLookupLog::query()
            ->withoutOrganizationScope()
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('fake', $log->provider);
        $this->assertSame('success', $log->status);
        $this->assertSame('ABC1D23', $log->plate);
    }
}
