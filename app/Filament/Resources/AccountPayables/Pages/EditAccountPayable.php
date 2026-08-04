<?php

namespace App\Filament\Resources\AccountPayables\Pages;

use App\Filament\Resources\AccountPayables\AccountPayableResource;
use App\Models\FinancialAccount;
use App\Services\Finance\PaymentService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditAccountPayable extends EditRecord
{
    protected static string $resource = AccountPayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Aprovar')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(
                    fn (): bool => in_array(
                        $this->record->status,
                        ['draft', 'awaiting_approval'],
                        true
                    )
                )
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        app(PaymentService::class)->approve($this->record);

                        Notification::make()
                            ->success()
                            ->title('Conta aprovada')
                            ->send();

                        $this->redirect(
                            AccountPayableResource::getUrl('edit', [
                                'record' => $this->record,
                            ])
                        );
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Não foi possível aprovar')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),

            Action::make('pay')
                ->label('Registrar pagamento')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->visible(
                    fn (): bool =>
                        in_array(
                            $this->record->status,
                            ['approved', 'overdue', 'partially_paid'],
                            true
                        )
                        && (float) $this->record->open_value > 0
                )
                ->schema([
                    TextInput::make('principal_value')
                        ->label('Valor principal')
                        ->numeric()
                        ->prefix('R$')
                        ->required()
                        ->default(fn (): float => (float) $this->record->open_value)
                        ->maxValue(fn (): float => (float) $this->record->open_value),

                    Select::make('payment_method')
                        ->label('Forma de pagamento')
                        ->required()
                        ->options([
                            'pix' => 'PIX',
                            'cash' => 'Dinheiro',
                            'card' => 'Cartão',
                            'boleto' => 'Boleto',
                            'transfer' => 'Transferência',
                            'ted' => 'TED',
                            'doc' => 'DOC',
                            'check' => 'Cheque',
                            'other' => 'Outro',
                        ]),

                    Select::make('financial_account_id')
                        ->label('Conta financeira')
                        ->required()
                        ->options(
                            fn (): array => FinancialAccount::query()
                                ->where('status', 'active')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all()
                        )
                        ->searchable(),

                    DateTimePicker::make('paid_at')
                        ->label('Pago em')
                        ->seconds(false)
                        ->native(false)
                        ->default(now())
                        ->required(),

                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    try {
                        $payment = app(PaymentService::class)
                            ->register($this->record, $data);

                        Notification::make()
                            ->success()
                            ->title('Pagamento registrado')
                            ->body("Pagamento {$payment->number}")
                            ->send();

                        $this->redirect(
                            AccountPayableResource::getUrl('edit', [
                                'record' => $this->record,
                            ])
                        );
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Não foi possível registrar o pagamento')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
