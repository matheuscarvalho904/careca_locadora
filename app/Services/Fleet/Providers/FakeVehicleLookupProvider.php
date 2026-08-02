<?php

namespace App\Services\Fleet\Providers;

use App\Contracts\Fleet\VehicleLookupProvider;
use App\Data\Fleet\VehicleLookupResult;
use InvalidArgumentException;

final class FakeVehicleLookupProvider implements VehicleLookupProvider
{
    public function lookup(string $plate): VehicleLookupResult
    {
        $plate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate) ?? '');

        if (! preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $plate)) {
            throw new InvalidArgumentException(
                'Informe uma placa válida no padrão antigo ou Mercosul.'
            );
        }

        return new VehicleLookupResult(
            plate: $plate,
            oldPlate: 'ABC1234',
            brand: 'FIAT',
            model: 'STRADA',
            version: 'FREEDOM 1.3',
            manufactureYear: 2024,
            modelYear: 2025,
            color: 'Branca',
            fuelType: 'Flex',
            transmission: 'Manual',
            seats: 5,
            chassis: '9BD00000000000001',
            renavam: '12345678901',
            engineDescription: '1.3',
            engineDisplacementCc: 1332,
            enginePowerHp: 107,
            axles: 2,
            grossWeightT: 1.78,
            maximumTractionCapacityT: 2.50,
            city: 'Aripuanã',
            state: 'MT',
            vehicleType: 'Automóvel',
            species: 'Passageiro',
            bodyType: 'Picape',
            origin: 'Nacional',
            segment: 'Comercial leve',
            subsegment: 'Picape compacta',
            situation: 'Regular',
            fipeCode: '001234-5',
            fipeDescription: 'Fiat Strada Freedom 1.3',
            fipeValue: 105000.00,
            fipeReferenceMonth: 'julho de 2026',
            fipeScore: 99,
            raw: [
                'provider' => 'fake',
                'plate' => $plate,
            ],
        );
    }

    public function name(): string
    {
        return 'fake';
    }
}
