<?php

namespace App\Filament\Resources\RentalReservations\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Reserva')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.display_name')
                    ->label('Cliente')
                    ->searchable(['legal_name', 'trade_name'])
                    ->wrap(),

                TextColumn::make('pickup_expected_at')
                    ->label('Retirada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('return_expected_at')
                    ->label('Devolução')
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
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'preparing' => 'info',
                        'converted' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmada',
                        'preparing' => 'Em preparação',
                        'converted' => 'Em locação',
                        'completed' => 'Concluída',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmada',
                        'preparing' => 'Em preparação',
                        'converted' => 'Em locação',
                        'completed' => 'Concluída',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->defaultSort('pickup_expected_at', 'desc');
    }
}
