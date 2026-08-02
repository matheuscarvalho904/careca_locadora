<?php

namespace App\Filament\Resources\CashMovements\Pages;

use App\Filament\Resources\CashMovements\CashMovementResource;
use App\Models\FinancialAccount;
use App\Services\Finance\TreasuryService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCashMovements extends ListRecords
{
    protected static string $resource = CashMovementResource::class;

    protected static ?string $title = 'Extrato financeiro';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('supply')
                ->label('Novo suprimento')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->schema($this->manualMovementSchema())
                ->action(function (array $data): void {
                    app(TreasuryService::class)->supply(
                        FinancialAccount::query()->findOrFail($data['financial_account_id']),
                        (float) $data['value'],
                        $data['description'],
                        $data,
                    );

                    Notification::make()
                        ->success()
                        ->title('Suprimento registrado')
                        ->send();
                }),

            Action::make('withdrawal')
                ->label('Nova sangria')
                ->icon('heroicon-o-minus-circle')
                ->color('danger')
                ->schema($this->manualMovementSchema())
                ->action(function (array $data): void {
                    app(TreasuryService::class)->withdrawal(
                        FinancialAccount::query()->findOrFail($data['financial_account_id']),
                        (float) $data['value'],
                        $data['description'],
                        $data,
                    );

                    Notification::make()
                        ->success()
                        ->title('Sangria registrada')
                        ->send();
                }),

            Action::make('transfer')
                ->label('Transferir entre contas')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->schema([
                    Select::make('source_account_id')
                        ->label('Conta de origem')
                        ->options($this->accountOptions())
                        ->searchable()
                        ->required(),

                    Select::make('destination_account_id')
                        ->label('Conta de destino')
                        ->options($this->accountOptions())
                        ->searchable()
                        ->required(),

                    TextInput::make('value')
                        ->label('Valor')
                        ->numeric()
                        ->prefix('R$')
                        ->required(),

                    DateTimePicker::make('occurred_at')
                        ->label('Data')
                        ->native(false)
                        ->seconds(false)
                        ->default(now())
                        ->required(),

                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    app(TreasuryService::class)->transfer(
                        FinancialAccount::query()->findOrFail($data['source_account_id']),
                        FinancialAccount::query()->findOrFail($data['destination_account_id']),
                        (float) $data['value'],
                        $data,
                    );

                    Notification::make()
                        ->success()
                        ->title('Transferência realizada')
                        ->send();
                }),
        ];
    }

    private function manualMovementSchema(): array
    {
        return [
            Select::make('financial_account_id')
                ->label('Conta financeira')
                ->options($this->accountOptions())
                ->searchable()
                ->required(),

            TextInput::make('value')
                ->label('Valor')
                ->numeric()
                ->prefix('R$')
                ->required(),

            TextInput::make('description')
                ->label('Descrição')
                ->required()
                ->maxLength(255),

            DateTimePicker::make('occurred_at')
                ->label('Data')
                ->native(false)
                ->seconds(false)
                ->default(now())
                ->required(),

            Textarea::make('notes')
                ->label('Observações')
                ->rows(3),
        ];
    }

    private function accountOptions(): array
    {
        return FinancialAccount::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
