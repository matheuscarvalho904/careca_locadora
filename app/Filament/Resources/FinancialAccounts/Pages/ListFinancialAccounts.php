<?php

namespace App\Filament\Resources\FinancialAccounts\Pages;

use App\Filament\Resources\FinancialAccounts\FinancialAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinancialAccounts extends ListRecords
{
    protected static string $resource = FinancialAccountResource::class;

    protected static ?string $title = 'Contas financeiras';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova conta financeira')
                ->icon('heroicon-o-plus'),
        ];
    }
}
