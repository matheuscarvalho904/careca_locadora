<?php

namespace App\Services\Fleet;

use App\Contracts\Fleet\VehicleLookupProvider;
use App\Services\Fleet\Providers\ApiPlacasVehicleLookupProvider;
use App\Services\Fleet\Providers\FakeVehicleLookupProvider;
use App\Services\Fleet\Providers\PuxaPlacaVehicleLookupProvider;
use InvalidArgumentException;

final class VehicleLookupManager
{
    public function provider(?string $provider = null): VehicleLookupProvider
    {
        $provider ??= (string) config('fleet.vehicle_lookup.provider');

        return match ($provider) {
            'api_placas' => app(ApiPlacasVehicleLookupProvider::class),
            'puxaplaca' => app(PuxaPlacaVehicleLookupProvider::class),
            'fake' => app(FakeVehicleLookupProvider::class),
            default => throw new InvalidArgumentException(
                "Provedor de consulta de placa não suportado: {$provider}"
            ),
        };
    }
}
