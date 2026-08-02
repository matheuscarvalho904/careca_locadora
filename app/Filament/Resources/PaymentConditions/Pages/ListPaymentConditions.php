<?php
namespace App\Filament\Resources\PaymentConditions\Pages;
use App\Filament\Resources\PaymentConditions\PaymentConditionResource;
use Filament\Resources\Pages\ListRecords;
class ListPaymentConditions extends ListRecords
{
    protected static string $resource = PaymentConditionResource::class;
    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()->label('Nova condição de pagamento')];
    }

}
