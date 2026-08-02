<?php

namespace App\Filament\Resources\RentalReturns\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Devolução')
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
                    ->label('Prevista')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('returned_at')
                    ->label('Devolvida')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Pendente')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Ativos')
                    ->counts('items')
                    ->badge(),

                TextColumn::make('total_charge_value')
                    ->label('Adicionais')
                    ->money('BRL')
                    ->sortable(),

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
                        'draft' => 'Em conferência',
                        'completed' => 'Devolvida',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Em conferência',
                        'completed' => 'Devolvida',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
