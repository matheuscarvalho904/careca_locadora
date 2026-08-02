<?php

namespace App\Filament\Resources\CashMovements\Tables;

use App\Services\Finance\TreasuryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CashMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Movimento')
                    ->searchable()
                    ->weight('bold')
                    ->width('145px')
                    ->extraAttributes(['class' => 'whitespace-nowrap']),

                TextColumn::make('occurred_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->width('155px')
                    ->extraAttributes(['class' => 'whitespace-nowrap']),

                TextColumn::make('financialAccount.name')
                    ->label('Conta')
                    ->searchable()
                    ->limit(28)
                    ->tooltip(fn ($record): ?string => $record->financialAccount?->name)
                    ->width('190px')
                    ->extraAttributes(['class' => 'whitespace-nowrap']),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string =>
                        $state === 'entry' ? 'success' : 'danger'
                    )
                    ->formatStateUsing(fn (string $state): string =>
                        $state === 'entry' ? 'Entrada' : 'Saída'
                    )
                    ->width('95px'),

                TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'receipt' => 'Recebimento',
                        'payment' => 'Pagamento',
                        'payment_reversal' => 'Estorno',
                        'transfer' => 'Transferência',
                        'supply' => 'Suprimento',
                        'withdrawal' => 'Sangria',
                        'adjustment' => 'Ajuste',
                        default => $state ?: 'Operação',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'receipt', 'supply' => 'success',
                        'payment', 'withdrawal' => 'danger',
                        'transfer' => 'info',
                        'payment_reversal' => 'warning',
                        default => 'gray',
                    })
                    ->width('130px'),

                TextColumn::make('value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->width('135px')
                    ->extraAttributes(['class' => 'whitespace-nowrap']),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'posted' => 'Efetivado',
                        'cancelled' => 'Cancelado',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'posted' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->width('110px'),

                TextColumn::make('reconciliation_status')
                    ->label('Conciliação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reconciled' => 'success',
                        'divergent' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'reconciled' => 'Conciliado',
                        'divergent' => 'Divergente',
                        default => 'Pendente',
                    })
                    ->width('130px'),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(52)
                    ->tooltip(fn ($record): string => (string) $record->description)
                    ->width('320px'),
            ])
            ->filters([
                SelectFilter::make('financial_account_id')
                    ->label('Conta')
                    ->relationship('financialAccount', 'name'),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'entry' => 'Entrada',
                        'exit' => 'Saída',
                    ]),

                SelectFilter::make('reconciliation_status')
                    ->label('Conciliação')
                    ->options([
                        'pending' => 'Pendente',
                        'reconciled' => 'Conciliado',
                        'divergent' => 'Divergente',
                    ]),
            ])
            ->recordActions([
                Action::make('reconcile')
                    ->label('Conciliar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool =>
                        $record->reconciliation_status !== 'reconciled'
                    )
                    ->requiresConfirmation()
                    ->action(fn ($record) =>
                        app(TreasuryService::class)->reconcile($record)
                    ),

                Action::make('divergent')
                    ->label('Divergência')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn ($record): bool =>
                        $record->reconciliation_status !== 'reconciled'
                    )
                    ->schema([
                        Textarea::make('reason')
                            ->label('Motivo')
                            ->required(),
                    ])
                    ->action(fn ($record, array $data) =>
                        app(TreasuryService::class)
                            ->markDivergent($record, $data['reason'])
                    ),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->striped();
    }
}
