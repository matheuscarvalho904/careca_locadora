<?php

namespace App\Filament\Resources\RentalDeliveries\Pages;

use App\Filament\Resources\RentalDeliveries\RentalDeliveryResource;
use App\Services\Rentals\RentalDeliveryService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRentalDelivery extends EditRecord
{
    protected static string $resource = RentalDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('complete')
                ->label('Concluir entrega')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'draft')
                ->action(function (): void {
                    $this->save();

                    app(RentalDeliveryService::class)
                        ->complete($this->record->fresh());

                    Notification::make()
                        ->success()
                        ->title('Entrega concluída')
                        ->body('Os ativos foram liberados ao cliente.')
                        ->send();

                    $this->redirect(
                        RentalDeliveryResource::getUrl('edit', [
                            'record' => $this->record,
                        ])
                    );
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->status === 'completed') {
            unset(
                $data['status'],
                $data['scheduled_at'],
                $data['customer_signer_name'],
                $data['employee_signer_name'],
                $data['customer_signature_path'],
                $data['employee_signature_path'],
                $data['photos'],
                $data['general_notes'],
            );
        }

        return $data;
    }
}
