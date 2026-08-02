<?php

namespace App\Filament\Resources\RentalReservations\Pages;

use App\Filament\Resources\RentalReservations\RentalReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalReservations extends ListRecords
{
    protected static string $resource = RentalReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nova reserva'),
        ];
    }
}
