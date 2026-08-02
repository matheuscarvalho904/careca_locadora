<?php

namespace App\Filament\Resources\RentalInvoices\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Fatura')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('contract.number')
                    ->label('Contrato')
                    ->searchable(),

                TextColumn::make('customer.display_name')
                    ->label('Cliente')
                    ->wrap(),

                TextColumn::make('issued_at')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->placeholder('Pendente')
                    ->sortable(),

                TextColumn::make('due_at')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_value')
                    ->label('Total')
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
                        'draft' => 'gray',
                        'issued' => 'warning',
                        'partially_paid' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'issued' => 'Emitida',
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
                        'draft' => 'Rascunho',
                        'issued' => 'Emitida',
                        'partially_paid' => 'Parcialmente recebida',
                        'paid' => 'Recebida',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('issued_at', 'desc');
    }
}
