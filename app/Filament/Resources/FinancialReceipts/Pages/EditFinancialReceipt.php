<?php

namespace App\Filament\Resources\FinancialReceipts\Pages;

use App\Filament\Resources\FinancialReceipts\FinancialReceiptResource;
use App\Services\Finance\ReceiptService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFinancialReceipt extends EditRecord
{
    protected static string $resource = FinancialReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reverse')
                ->label('Estornar recebimento')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'confirmed')
                ->schema([
                    Textarea::make('reason')
                        ->label('Motivo do estorno')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    app(ReceiptService::class)->reverse(
                        $this->record,
                        $data['reason'],
                    );

                    Notification::make()
                        ->success()
                        ->title('Recebimento estornado')
                        ->send();

                    $this->redirect(
                        FinancialReceiptResource::getUrl('edit', [
                            'record' => $this->record,
                        ])
                    );
                }),
        ];
    }
}
