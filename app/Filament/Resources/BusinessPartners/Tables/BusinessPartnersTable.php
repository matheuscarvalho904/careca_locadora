<?php

namespace App\Filament\Resources\BusinessPartners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BusinessPartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('display_name')
                    ->label('Nome')
                    ->description(fn ($record): string => $record->legal_name)
                    ->searchable(['legal_name', 'trade_name'])
                    ->sortable()
                    ->wrap(),

                TextColumn::make('roles')
                    ->label('Papéis')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => collect($state ?? [])
                        ->map(fn (string $role): string => match ($role) {
                            'customer' => 'Cliente',
                            'supplier' => 'Fornecedor',
                            'carrier' => 'Transportador',
                            'service_provider' => 'Prestador',
                            default => $role,
                        })
                        ->implode(', ')),

                TextColumn::make('document')
                    ->label('CPF/CNPJ')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('credit_limit')
                    ->label('Limite')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'blocked' => 'Bloqueado',
                        default => 'Inativo',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
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
            ->defaultSort('legal_name');
    }
}
