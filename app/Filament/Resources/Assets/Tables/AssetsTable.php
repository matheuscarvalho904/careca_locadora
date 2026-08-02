<?php

namespace App\Filament\Resources\Assets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prefix')
                    ->label('Prefixo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Ativo')
                    ->description(
                        fn ($record): string =>
                            collect([$record->brand, $record->model, $record->plate])
                                ->filter()
                                ->implode(' • ')
                    )
                    ->searchable(['name', 'brand', 'model', 'plate'])
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('plate')
                    ->label('Placa')
                    ->searchable()
                    ->placeholder('Sem placa'),

                TextColumn::make('operational_status')
                    ->label('Operacional')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'in_use' => 'info',
                        'maintenance' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponível',
                        'in_use' => 'Em uso',
                        'maintenance' => 'Em manutenção',
                        default => 'Bloqueado',
                    }),

                TextColumn::make('rental_status')
                    ->label('Locação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'rented' => 'info',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponível',
                        'reserved' => 'Reservado',
                        'rented' => 'Locado',
                        default => 'Bloqueado',
                    }),

                TextColumn::make('current_odometer')
                    ->label('KM')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' km')
                    ->toggleable(),

                TextColumn::make('current_hourmeter')
                    ->label('Horímetro')
                    ->numeric(decimalPlaces: 1)
                    ->suffix(' h')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('operational_status')
                    ->label('Situação operacional')
                    ->options([
                        'available' => 'Disponível',
                        'in_use' => 'Em uso',
                        'maintenance' => 'Em manutenção',
                        'blocked' => 'Bloqueado',
                    ]),

                SelectFilter::make('rental_status')
                    ->label('Situação da locação')
                    ->options([
                        'available' => 'Disponível',
                        'reserved' => 'Reservado',
                        'rented' => 'Locado',
                        'blocked' => 'Bloqueado',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionados'),
                ]),
            ])
            ->defaultSort('prefix');
    }
}
