<?php
namespace App\Filament\Resources\Quotations\Pages;
use App\Filament\Resources\Quotations\QuotationResource;
use Filament\Resources\Pages\ListRecords;
class ListQuotations extends ListRecords
{
    protected static string $resource = QuotationResource::class;
    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()->label('Nova cotação')];
    }

}
