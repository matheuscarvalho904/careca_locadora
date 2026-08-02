<?php

namespace App\Filament\Resources\FinancialAccounts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinancialAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Conta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Caixa',
                        'bank' => 'Bancária',
                        'digital' => 'Digital',
                        default => $state,
                    }),

                TextColumn::make('bank_name')
                    ->label('Banco')
                    ->placeholder('—'),

                TextColumn::make('current_balance')
                    ->label('Saldo atual')
                    ->money('BRL'),

                IconColumn::make('is_default')
                    ->label('Padrão')
                    ->boolean(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string =>
                        $state === 'active' ? 'success' : 'gray'
                    )
                    ->formatStateUsing(fn (string $state): string =>
                        $state === 'active' ? 'Ativa' : 'Inativa'
                    ),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ]);
    }
}
