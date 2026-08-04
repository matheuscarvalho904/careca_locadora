<?php
namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Filament\Resources\PurchaseReceipts\PurchaseReceiptResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Visualizar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn (): string => route('purchase-orders.pdf', [
                    'purchaseOrder' => $this->record,
                ]))
                ->openUrlInNewTab(),

            Action::make('approve')
                ->label('Aprovar OC')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'draft')
                ->action(function (): void {
                    $this->record->update(['status' => 'approved']);

                    Notification::make()
                        ->success()
                        ->title('Ordem de Compra aprovada')
                        ->send();
                }),

            Action::make('receive')
                ->label('Receber mercadoria')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('info')
                ->visible(fn (): bool => in_array(
                    $this->record->status,
                    ['approved', 'sent', 'partially_received'],
                    true
                ))
                ->url(fn (): string => PurchaseReceiptResource::getUrl('create', [
                    'purchase_order_id' => $this->record->id,
                ])),

            DeleteAction::make()
                ->visible(fn (): bool => $this->record->status === 'draft'),
        ];
    }
}
