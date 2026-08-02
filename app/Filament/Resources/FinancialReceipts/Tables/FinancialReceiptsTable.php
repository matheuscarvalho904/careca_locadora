<?php

namespace App\Filament\Resources\FinancialReceipts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FinancialReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Recebimento')->searchable()->weight('bold'),
                TextColumn::make('receivable.number')->label('Conta'),
                TextColumn::make('customer.display_name')->label('Cliente')->wrap(),
                TextColumn::make('received_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('payment_method')->label('Forma')->badge(),
                TextColumn::make('total_received')->label('Valor')->money('BRL')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'confirmed' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === 'confirmed' ? 'Confirmado' : 'Estornado'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'confirmed' => 'Confirmado',
                        'reversed' => 'Estornado',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('received_at', 'desc');
    }
}
