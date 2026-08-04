<?php

namespace App\Filament\Resources\InventoryBalances;

use App\Filament\Resources\InventoryBalances\Pages\ListInventoryBalances;
use App\Models\InventoryBalance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class InventoryBalanceResource extends Resource
{
    protected static ?string $model = InventoryBalance::class;
    protected static ?string $modelLabel = 'saldo de estoque';
    protected static ?string $pluralModelLabel = 'saldos de estoque';
    protected static ?string $navigationLabel = 'Saldos de estoque';
    protected static string | UnitEnum | null $navigationGroup = 'Estoque';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.code')
                    ->label('Código')
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('warehouse.name')
                    ->label('Almoxarifado')
                    ->searchable(),

                TextColumn::make('quantity_on_hand')
                    ->label('Saldo físico')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),

                TextColumn::make('quantity_reserved')
                    ->label('Reservado')
                    ->numeric(decimalPlaces: 4),

                TextColumn::make('available_quantity')
                    ->label('Disponível')
                    ->numeric(decimalPlaces: 4),

                TextColumn::make('average_cost')
                    ->label('Custo médio')
                    ->money('BRL', divideBy: 1),

                TextColumn::make('inventory_value')
                    ->label('Valor em estoque')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('last_movement_at')
                    ->label('Última movimentação')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Almoxarifado')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('last_movement_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryBalances::route('/'),
        ];
    }
}
