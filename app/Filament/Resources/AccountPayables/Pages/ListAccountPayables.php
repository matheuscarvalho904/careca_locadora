<?php
namespace App\Filament\Resources\AccountPayables\Pages;
use App\Filament\Resources\AccountPayables\AccountPayableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListAccountPayables extends ListRecords
{
    protected static string $resource=AccountPayableResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Nova conta a pagar')->icon('heroicon-o-plus')]; }
}
