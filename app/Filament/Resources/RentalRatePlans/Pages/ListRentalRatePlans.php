<?php

namespace App\Filament\Resources\RentalRatePlans\Pages;

use App\Filament\Resources\RentalRatePlans\RentalRatePlanResource;
use Filament\Resources\Pages\ListRecords;

class ListRentalRatePlans extends ListRecords
{
    protected static string $resource = RentalRatePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()->label('Nova tarifa')];
    }
}
