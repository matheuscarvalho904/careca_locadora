<?php

namespace App\Filament\Resources\AccountsReceivable\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountsReceivableTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('invoice.number')
                    ->label('Fatura')
                    ->searchable(),

                TextColumn::make('customer.display_name')
                    ->label('Cliente')
                    ->wrap(),

                TextColumn::make('installment_number')
                    ->label('Parcela')
                    ->formatStateUsing(fn ($record): string =>
                        "{$record->installment_number}/{$record->installments_count}"
                    ),

                TextColumn::make('due_at')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('original_value')
                    ->label('Original')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('open_value')
                    ->label('Em aberto')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'overdue' => 'danger',
                        'partially_paid' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Em aberto',
                        'overdue' => 'Vencida',
                        'partially_paid' => 'Parcialmente recebida',
                        'paid' => 'Recebida',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Em aberto',
                        'overdue' => 'Vencida',
                        'partially_paid' => 'Parcialmente recebida',
                        'paid' => 'Recebida',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('due_at');
    }
}
