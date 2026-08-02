<?php

namespace App\Filament\Resources\Warehouses;

use App\Filament\Resources\Warehouses\Pages\CreateWarehouse;
use App\Filament\Resources\Warehouses\Pages\EditWarehouse;
use App\Filament\Resources\Warehouses\Pages\ListWarehouses;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;
    protected static ?string $modelLabel = 'almoxarifado';
    protected static ?string $pluralModelLabel = 'almoxarifados';
    protected static ?string $navigationLabel = 'Almoxarifados';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Almoxarifado')->columns(3)->schema([
                TextInput::make('code')->label('Código')->required()->maxLength(40),
                TextInput::make('name')->label('Nome')->required()->maxLength(150),
                Select::make('company_id')->label('Empresa')->relationship('company', 'legal_name')->searchable(),
                Select::make('branch_id')->label('Filial')->relationship('branch', 'legal_name')->searchable(),
                Select::make('status')->label('Status')->options([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                ])->default('active')->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable(),
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('company.legal_name')->label('Empresa')->limit(30),
            TextColumn::make('branch.legal_name')->label('Filial')->limit(30),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')
                ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }
}
