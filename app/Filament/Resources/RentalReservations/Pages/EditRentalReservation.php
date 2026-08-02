<?php

namespace App\Filament\Resources\RentalReservations\Pages;

use App\Filament\Resources\RentalContracts\RentalContractResource;
use App\Filament\Resources\RentalReservations\RentalReservationResource;
use App\Services\Rentals\RentalReservationService;
use App\Services\Rentals\ReservationToContractService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRentalReservation extends EditRecord
{
    protected static string $resource = RentalReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convertToContract')
                ->label('Converter em contrato')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool =>
                    ! in_array($this->record->status, [
                        'cancelled',
                        'converted',
                        'completed',
                    ], true)
                )
                ->action(function (): void {
                    $contract = app(ReservationToContractService::class)
                        ->convert($this->record);

                    Notification::make()
                        ->success()
                        ->title('Contrato gerado com sucesso')
                        ->body("Contrato {$contract->number}")
                        ->send();

                    $this->redirect(
                        RentalContractResource::getUrl('edit', [
                            'record' => $contract,
                        ])
                    );
                }),

            DeleteAction::make()->label('Excluir'),
        ];
    }

    protected function afterSave(): void
    {
        app(RentalReservationService::class)->recalculate($this->record);
    }
}
