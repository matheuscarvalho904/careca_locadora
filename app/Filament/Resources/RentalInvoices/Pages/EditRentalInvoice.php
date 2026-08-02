<?php

namespace App\Filament\Resources\RentalInvoices\Pages;

use App\Filament\Resources\RentalInvoices\RentalInvoiceResource;
use App\Services\Rentals\RentalInvoiceService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRentalInvoice extends EditRecord
{
    protected static string $resource = RentalInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Baixar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route(
                    'rental-invoices.pdf',
                    ['invoice' => $this->record->getKey()]
                ))
                ->openUrlInNewTab(),

            Action::make('issue')
                ->label(fn (): string =>
                    $this->record->status === 'draft'
                        ? 'Emitir fatura'
                        : 'Gerar contas a receber'
                )
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (): bool =>
                    $this->record->status !== 'cancelled'
                    && $this->record->receivables()->doesntExist()
                )
                ->schema([
                    TextInput::make('installments')
                        ->label('Quantidade de parcelas')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(120),
                ])
                ->action(function (array $data): void {
                    $this->save();

                    app(RentalInvoiceService::class)->issue(
                        invoice: $this->record->fresh(),
                        installments: (int) $data['installments'],
                    );

                    Notification::make()
                        ->success()
                        ->title('Fatura processada')
                        ->body('As contas a receber foram geradas.')
                        ->send();

                    $this->redirect(
                        RentalInvoiceResource::getUrl('edit', [
                            'record' => $this->record,
                        ])
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        app(RentalInvoiceService::class)
            ->recalculate($this->record->fresh());

        $this->record->refresh();
    }
}
