<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $modelLabel = 'movimentação de estoque';
    protected static ?string $pluralModelLabel = 'movimentações de estoque';
    protected static ?string $navigationLabel = 'Kardex';
    protected static string | UnitEnum | null $navigationGroup = 'Estoque';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Movimento')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('occurred_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase_receipt' => 'Entrada por recebimento',
                        'issue' => 'Saída',
                        'transfer' => 'Transferência',
                        'adjustment' => 'Ajuste',
                        'inventory' => 'Inventário',
                        'return' => 'Devolução',
                        default => $state,
                    }),

                TextColumn::make('product.code')
                    ->label('Código')
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('warehouse.name')
                    ->label('Almoxarifado'),

                TextColumn::make('direction')
                    ->label('Mov.')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Entrada',
                        'out' => 'Saída',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->numeric(decimalPlaces: 4),

                TextColumn::make('unit_cost')
                    ->label('Custo unitário')
                    ->money('BRL'),

                TextColumn::make('balance_after')
                    ->label('Saldo após')
                    ->numeric(decimalPlaces: 4),

                TextColumn::make('purchaseReceipt.number')
                    ->label('Recebimento')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Almoxarifado')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'purchase_receipt' => 'Entrada por recebimento',
                        'issue' => 'Saída',
                        'transfer' => 'Transferência',
                        'adjustment' => 'Ajuste',
                        'inventory' => 'Inventário',
                        'return' => 'Devolução',
                    ]),
            ])
            ->defaultSort('occurred_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
        ];
    }
}
