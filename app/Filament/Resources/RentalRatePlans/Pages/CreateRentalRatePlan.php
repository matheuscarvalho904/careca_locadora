<?php

namespace App\Filament\Resources\RentalRatePlans\Pages;

use App\Filament\Resources\RentalRatePlans\RentalRatePlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRentalRatePlan extends CreateRecord
{
    protected static string $resource = RentalRatePlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $organizationId = auth()->user()?->organization_id
                ?? app(\App\Support\Tenancy\TenantContext::class)
                    ->organizationId();

            if (blank($organizationId)) {
                throw new \RuntimeException(
                    'Organização não identificada para gerar a tarifa.'
                );
            }

            $data['organization_id'] = $organizationId;
            $data['code'] = app(
                \App\Services\Numbering\NumberSequenceService::class
            )->next(
                organizationId: $organizationId,
                key: 'rental_rate_plan',
                name: 'Tarifas de locação',
                prefix: 'TAR-',
                padding: 4,
            );
        }

        return $data;
    }
}
