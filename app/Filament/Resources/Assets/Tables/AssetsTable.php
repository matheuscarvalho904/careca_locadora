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
                    ->weight('bold')
                    ->width('90px'),

                TextColumn::make('name')
                    ->label('Ativo')
                    ->searchable(['name', 'brand', 'model', 'plate'])
                    ->sortable()
                    ->limit(42)
                    ->tooltip(fn ($record): ?string => $record->name)
                    ->description(
                        fn ($record): ?string =>
                            collect([$record->brand, $record->model])
                                ->filter()
                                ->implode(' • ')
                                ?: null
                    )
                    ->width('280px'),

                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->width('150px'),

                TextColumn::make('branch.name')
                    ->label('Filial')
                    ->placeholder('Sem filial')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->width('150px'),

                TextColumn::make('plate')
                    ->label('Placa')
                    ->searchable()
                    ->placeholder('Sem placa')
                    ->copyable()
                    ->width('110px'),

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
                    })
                    ->width('120px'),

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
                    })
                    ->width('110px'),

                TextColumn::make('current_odometer')
                    ->label('KM')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' km')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('current_hourmeter')
                    ->label('Horímetro')
                    ->numeric(decimalPlaces: 1)
                    ->suffix(' h')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Filial')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

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
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir selecionados'),
                ]),
            ])
            ->defaultSort('prefix')
            ->striped();
    }
}
