<?php

namespace App\Filament\Resources\RentalReservations\Pages;

use App\Filament\Resources\RentalReservations\RentalReservationResource;
use App\Services\Rentals\RentalReservationService;
use Filament\Resources\Pages\CreateRecord;

class CreateRentalReservation extends CreateRecord
{
    protected static string $resource = RentalReservationResource::class;

    protected function afterCreate(): void
    {
        app(RentalReservationService::class)->recalculate($this->record);
    }
}
