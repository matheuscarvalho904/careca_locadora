<?php

namespace App\Filament\Resources\RentalContracts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Contrato')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.display_name')
                    ->label('Cliente')
                    ->searchable(['legal_name', 'trade_name'])
                    ->wrap(),

                TextColumn::make('reservation.number')
                    ->label('Reserva')
                    ->placeholder('Sem reserva'),

                TextColumn::make('starts_at')
                    ->label('Início')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Término')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Ativos')
                    ->counts('items')
                    ->badge(),

                TextColumn::make('total_value')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'awaiting_signature' => 'warning',
                        'active' => 'success',
                        'closed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'awaiting_signature' => 'Aguardando assinatura',
                        'active' => 'Ativo',
                        'closed' => 'Encerrado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'awaiting_signature' => 'Aguardando assinatura',
                        'active' => 'Ativo',
                        'closed' => 'Encerrado',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
