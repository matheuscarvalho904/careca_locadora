<?php

namespace App\Filament\Resources\RentalReturns\Pages;

use App\Filament\Resources\RentalReturns\RentalReturnResource;
use App\Services\Rentals\RentalReturnService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRentalReturn extends EditRecord
{
    protected static string $resource = RentalReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('checklist_premium')
                ->label('Checklist Premium')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->url(fn (): string => RentalReturnResource::getUrl(
                    'checklist-premium',
                    ['record' => $this->record]
                )),

            Action::make('damage_map')
                ->label('Comparar avarias')
                ->icon('heroicon-o-map')
                ->color('warning')
                ->url(fn (): string => RentalReturnResource::getUrl(
                    'damage-map',
                    ['record' => $this->record]
                )),

            Action::make('recalculate')
                ->label('Recalcular cobranças')
                ->icon('heroicon-o-calculator')
                ->visible(fn (): bool => $this->record->status === 'draft')
                ->action(function (): void {
                    $this->save();

                    app(RentalReturnService::class)
                        ->recalculate($this->record->fresh());

                    Notification::make()
                        ->success()
                        ->title('Cobranças recalculadas')
                        ->send();

                    $this->reloadRecordPage();
                }),

            Action::make('complete')
                ->label('Concluir devolução')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'draft')
                ->action(function (): void {
                    $this->save();

                    app(RentalReturnService::class)
                        ->complete($this->record->fresh());

                    Notification::make()
                        ->success()
                        ->title('Devolução concluída')
                        ->body('O contrato foi encerrado e os ativos foram liberados.')
                        ->send();

                    $this->reloadRecordPage();
                }),
        ];
    }

    protected function afterSave(): void
    {
        app(RentalReturnService::class)
            ->recalculate($this->record->fresh());

        $this->record->refresh();
    }

    private function reloadRecordPage(): void
    {
        $this->record->refresh();

        $this->redirect(
            RentalReturnResource::getUrl('edit', [
                'record' => $this->record,
            ])
        );
    }
}
