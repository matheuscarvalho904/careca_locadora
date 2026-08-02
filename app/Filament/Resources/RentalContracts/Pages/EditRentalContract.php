<?php

namespace App\Filament\Resources\RentalContracts\Pages;

use App\Filament\Resources\RentalContracts\RentalContractResource;
use App\Filament\Resources\RentalDeliveries\RentalDeliveryResource;
use App\Filament\Resources\RentalReturns\RentalReturnResource;
use App\Services\Rentals\RentalDeliveryService;
use App\Services\Rentals\RentalReturnService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRentalContract extends EditRecord
{
    protected static string $resource = RentalContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateInvoice')
                ->label('Gerar fatura de locação')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->visible(fn (): bool =>
                    $this->record->status === 'closed'
                    && $this->record->rentalInvoice()->doesntExist()
                )
                ->action(function (): void {
                    $invoice = app(\App\Services\Rentals\RentalInvoiceService::class)
                        ->createFromContract($this->record);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Fatura de locação gerada')
                        ->body("Fatura {$invoice->number}")
                        ->send();

                    $this->redirect(
                        \App\Filament\Resources\RentalInvoices\RentalInvoiceResource::getUrl('edit', [
                            'record' => $invoice,
                        ])
                    );
                }),

            Action::make('openInvoice')
                ->label('Abrir fatura de locação')
                ->icon('heroicon-o-document-currency-dollar')
                ->visible(fn (): bool => $this->record->rentalInvoice()->exists())
                ->action(function (): void {
                    $invoice = $this->record->rentalInvoice()->firstOrFail();

                    $this->redirect(
                        \App\Filament\Resources\RentalInvoices\RentalInvoiceResource::getUrl('edit', [
                            'record' => $invoice,
                        ])
                    );
                }),
            Action::make('awaitingSignature')
                ->label('Enviar para assinatura')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (): bool => $this->record->status === 'draft')
                ->action(function (): void {
                    $this->record->update([
                        'status' => 'awaiting_signature',
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Contrato aguardando assinatura')
                        ->send();

                    $this->reloadRecordPage();
                }),

            Action::make('activate')
                ->label('Ativar contrato')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool =>
                    in_array($this->record->status, [
                        'draft',
                        'awaiting_signature',
                    ], true)
                )
                ->action(function (): void {
                    $now = now();

                    $this->record->update([
                        'status' => 'active',
                        'signed_at' => $this->record->signed_at ?? $now,
                        'activated_at' => $now,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Contrato ativado')
                        ->send();

                    $this->reloadRecordPage();
                }),

            Action::make('startDelivery')
                ->label('Iniciar entrega')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->visible(fn (): bool =>
                    $this->record->status === 'active'
                    && $this->record->delivery()->doesntExist()
                )
                ->action(function (): void {
                    $delivery = app(RentalDeliveryService::class)
                        ->createFromContract($this->record);

                    Notification::make()
                        ->success()
                        ->title('Checklist de entrega criado')
                        ->body("Entrega {$delivery->number}")
                        ->send();

                    $this->redirect(
                        RentalDeliveryResource::getUrl('edit', [
                            'record' => $delivery,
                        ])
                    );
                }),

            Action::make('openDelivery')
                ->label('Abrir entrega')
                ->icon('heroicon-o-truck')
                ->visible(fn (): bool => $this->record->delivery()->exists())
                ->action(function (): void {
                    $delivery = $this->record->delivery()->firstOrFail();

                    $this->redirect(
                        RentalDeliveryResource::getUrl('edit', [
                            'record' => $delivery,
                        ])
                    );
                }),

            Action::make('startReturn')
                ->label('Iniciar devolução')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('info')
                ->visible(fn (): bool =>
                    $this->record->status === 'active'
                    && $this->record->delivery?->status === 'completed'
                    && $this->record->rentalReturn()->doesntExist()
                )
                ->action(function (): void {
                    $return = app(RentalReturnService::class)
                        ->createFromContract($this->record);

                    Notification::make()
                        ->success()
                        ->title('Checklist de devolução criado')
                        ->body("Devolução {$return->number}")
                        ->send();

                    $this->redirect(
                        RentalReturnResource::getUrl('edit', [
                            'record' => $return,
                        ])
                    );
                }),

            Action::make('openReturn')
                ->label('Abrir devolução')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn (): bool => $this->record->rentalReturn()->exists())
                ->action(function (): void {
                    $return = $this->record->rentalReturn()->firstOrFail();

                    $this->redirect(
                        RentalReturnResource::getUrl('edit', [
                            'record' => $return,
                        ])
                    );
                }),
        ];
    }

    private function reloadRecordPage(): void
    {
        $this->record->refresh();

        $this->redirect(
            RentalContractResource::getUrl('edit', [
                'record' => $this->record,
            ])
        );
    }
}
