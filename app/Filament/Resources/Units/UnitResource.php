<?php

namespace App\Filament\Resources\Units;

use App\Filament\Resources\Units\Pages\CreateUnit;
use App\Filament\Resources\Units\Pages\EditUnit;
use App\Filament\Resources\Units\Pages\ListUnits;
use App\Models\Unit;
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

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;
    protected static ?string $modelLabel = 'unidade';
    protected static ?string $pluralModelLabel = 'unidades';
    protected static ?string $navigationLabel = 'Unidades';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-scale';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Unidade')->columns(3)->schema([
                TextInput::make('name')->label('Nome')->required()->maxLength(80),
                TextInput::make('symbol')->label('Sigla')->required()->maxLength(20),
                Select::make('status')->label('Status')->options([
                    'active' => 'Ativa',
                    'inactive' => 'Inativa',
                ])->default('active')->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('symbol')->label('Sigla')->searchable(),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativa' : 'Inativa')
                ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnits::route('/'),
            'create' => CreateUnit::route('/create'),
            'edit' => EditUnit::route('/{record}/edit'),
        ];
    }
}
