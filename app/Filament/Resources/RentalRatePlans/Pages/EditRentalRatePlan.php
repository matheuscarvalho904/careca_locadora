<?php

namespace App\Filament\Resources\RentalRatePlans\Pages;

use App\Filament\Resources\RentalRatePlans\RentalRatePlanResource;
use Filament\Resources\Pages\EditRecord;

class EditRentalRatePlan extends EditRecord
{
    protected static string $resource = RentalRatePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()->label('Excluir')];
    }
}
