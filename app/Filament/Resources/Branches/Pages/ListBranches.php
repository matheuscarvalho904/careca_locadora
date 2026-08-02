<?php
namespace App\Filament\Resources\Branches\Pages;
use App\Filament\Resources\Branches\BranchResource;
use Filament\Resources\Pages\ListRecords;
class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;
    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()->label('Novo cadastro')];
    }

}
