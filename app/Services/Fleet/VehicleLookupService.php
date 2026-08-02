<?php

namespace App\Services\Fleet;

use App\Data\Fleet\VehicleLookupResult;
use App\Models\VehicleLookupLog;
use Illuminate\Support\Facades\Auth;
use Throwable;

final class VehicleLookupService
{
    public function __construct(
        private readonly VehicleLookupManager $manager,
    ) {
    }

    public function lookup(
        string $organizationId,
        string $plate,
        ?string $assetId = null,
    ): VehicleLookupResult {
        $provider = $this->manager->provider();
        $startedAt = microtime(true);

        try {
            $result = $provider->lookup($plate);

            VehicleLookupLog::query()
                ->withoutOrganizationScope()
                ->create([
                    'organization_id' => $organizationId,
                    'asset_id' => $assetId,
                    'user_id' => Auth::id(),
                    'provider' => $provider->name(),
                    'plate' => $result->plate,
                    'status' => 'success',
                    'response' => $result->raw,
                    'duration_ms' => $this->duration($startedAt),
                    'consulted_at' => now(),
                ]);

            return $result;
        } catch (Throwable $exception) {
            VehicleLookupLog::query()
                ->withoutOrganizationScope()
                ->create([
                    'organization_id' => $organizationId,
                    'asset_id' => $assetId,
                    'user_id' => Auth::id(),
                    'provider' => $provider->name(),
                    'plate' => strtoupper(
                        preg_replace('/[^A-Za-z0-9]/', '', $plate) ?? ''
                    ),
                    'status' => 'failed',
                    'message' => mb_substr($exception->getMessage(), 0, 500),
                    'duration_ms' => $this->duration($startedAt),
                    'consulted_at' => now(),
                ]);

            throw $exception;
        }
    }

    private function duration(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
