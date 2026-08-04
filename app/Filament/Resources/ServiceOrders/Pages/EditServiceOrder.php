<?php
namespace App\Filament\Resources\ServiceOrders\Pages;
use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
class EditServiceOrder extends EditRecord
{
    protected static string $resource = ServiceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Visualizar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn (): string => route('service-orders.pdf', [
                    'serviceOrder' => $this->record,
                ]))
                ->openUrlInNewTab(),
        ];
    }
}