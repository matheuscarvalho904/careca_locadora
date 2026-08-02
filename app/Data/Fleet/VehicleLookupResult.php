<?php

namespace App\Data\Fleet;

final readonly class VehicleLookupResult
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $plate,
        public ?string $oldPlate = null,
        public ?string $brand = null,
        public ?string $model = null,
        public ?string $version = null,
        public ?int $manufactureYear = null,
        public ?int $modelYear = null,
        public ?string $color = null,
        public ?string $fuelType = null,
        public ?string $transmission = null,
        public ?int $seats = null,
        public ?string $chassis = null,
        public ?string $renavam = null,
        public ?string $engineNumber = null,
        public ?string $engineDescription = null,
        public ?int $engineDisplacementCc = null,
        public ?int $enginePowerHp = null,
        public ?int $axles = null,
        public ?float $grossWeightT = null,
        public ?float $maximumTractionCapacityT = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $vehicleType = null,
        public ?string $species = null,
        public ?string $bodyType = null,
        public ?string $origin = null,
        public ?string $segment = null,
        public ?string $subsegment = null,
        public ?string $situation = null,
        public ?string $fipeCode = null,
        public ?string $fipeDescription = null,
        public ?float $fipeValue = null,
        public ?string $fipeReferenceMonth = null,
        public ?int $fipeScore = null,
        public array $raw = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toAssetData(): array
    {
        return array_filter([
            'plate' => $this->plate,
            'old_plate' => $this->oldPlate,
            'brand' => $this->brand,
            'model' => $this->model,
            'version' => $this->version,
            'manufacture_year' => $this->manufactureYear,
            'model_year' => $this->modelYear,
            'color' => $this->color,
            'fuel_type' => $this->fuelType,
            'transmission' => $this->transmission,
            'seats' => $this->seats,
            'chassis' => $this->chassis,
            'renavam' => $this->renavam,
            'engine_number' => $this->engineNumber,
            'engine_description' => $this->engineDescription,
            'engine_displacement_cc' => $this->engineDisplacementCc,
            'engine_power_hp' => $this->enginePowerHp,
            'axles' => $this->axles,
            'gross_weight_t' => $this->grossWeightT,
            'maximum_traction_capacity_t' => $this->maximumTractionCapacityT,
            'registration_city' => $this->city,
            'registration_state' => $this->state,
            'species' => $this->species,
            'origin' => $this->origin,
            'segment' => $this->segment,
            'subsegment' => $this->subsegment,
            'external_situation' => $this->situation,
            'fipe_code' => $this->fipeCode,
            'fipe_description' => $this->fipeDescription,
            'fipe_value' => $this->fipeValue,
            'fipe_reference_month' => $this->fipeReferenceMonth,
            'fipe_score' => $this->fipeScore,
            'external_data' => [
                'vehicle_lookup' => [
                    'vehicle_type' => $this->vehicleType,
                    'body_type' => $this->bodyType,
                    'raw' => $this->raw,
                ],
            ],
            'external_data_synced_at' => now(),
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function suggestedName(): string
    {
        return collect([
            $this->brand,
            $this->model,
            $this->fuelType,
            $this->modelYear,
        ])->filter()->implode(' ');
    }
}
