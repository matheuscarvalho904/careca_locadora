<?php

namespace App\Filament\Resources\ApplicationCenters\Pages;

use App\Filament\Resources\ApplicationCenters\ApplicationCenterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApplicationCenters extends ListRecords
{
    protected static string $resource = ApplicationCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo centro de aplicação')
                ->icon('heroicon-o-plus'),
        ];
    }
}
