<?php

namespace App\Filament\Resources\AccountsReceivable\Pages;

use App\Filament\Resources\AccountsReceivable\AccountReceivableResource;
use App\Models\FinancialAccount;
use App\Services\Finance\ReceiptService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAccountReceivable extends EditRecord
{
    protected static string $resource = AccountReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('receive')
                ->label('Registrar recebimento')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn (): bool =>
                    $this->record->status !== 'cancelled'
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

                    TextInput::make('interest_value')->label('Juros')->numeric()->prefix('R$')->default(0),
                    TextInput::make('penalty_value')->label('Multa')->numeric()->prefix('R$')->default(0),
                    TextInput::make('discount_value')->label('Desconto')->numeric()->prefix('R$')->default(0),
                    TextInput::make('additional_value')->label('Acréscimo')->numeric()->prefix('R$')->default(0),

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
                        ->label('Caixa ou conta bancária')
                        ->options(fn (): array => FinancialAccount::query()
                            ->where('status', 'active')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable(),

                    DateTimePicker::make('received_at')
                        ->label('Recebido em')
                        ->seconds(false)
                        ->native(false)
                        ->default(now())
                        ->required(),

                    TextInput::make('payment_reference')
                        ->label('Referência')
                        ->maxLength(180),

                    FileUpload::make('proof_path')
                        ->label('Comprovante')
                        ->directory('financial-receipts/proofs')
                        ->visibility('private'),

                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $receipt = app(ReceiptService::class)->register(
                        $this->record,
                        $data,
                    );

                    Notification::make()
                        ->success()
                        ->title('Recebimento registrado')
                        ->body("Recebimento {$receipt->number}")
                        ->send();

                    $this->redirect(
                        AccountReceivableResource::getUrl('edit', [
                            'record' => $this->record,
                        ])
                    );
                }),
        ];
    }
}
