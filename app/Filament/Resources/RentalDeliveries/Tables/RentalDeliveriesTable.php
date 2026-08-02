<?php

namespace App\Filament\Resources\RentalDeliveries\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalDeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Entrega')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('contract.number')
                    ->label('Contrato')
                    ->searchable(),

                TextColumn::make('contract.customer.display_name')
                    ->label('Cliente')
                    ->wrap(),

                TextColumn::make('scheduled_at')
                    ->label('Agendada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('delivered_at')
                    ->label('Entregue')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Pendente')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Ativos')
                    ->counts('items')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Em preparação',
                        'completed' => 'Entregue',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Em preparação',
                        'completed' => 'Entregue',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
