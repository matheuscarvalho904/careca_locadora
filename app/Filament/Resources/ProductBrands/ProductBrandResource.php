<?php

namespace App\Filament\Resources\ProductBrands;

use App\Filament\Resources\ProductBrands\Pages\CreateProductBrand;
use App\Filament\Resources\ProductBrands\Pages\EditProductBrand;
use App\Filament\Resources\ProductBrands\Pages\ListProductBrands;
use App\Models\ProductBrand;
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

class ProductBrandResource extends Resource
{
    protected static ?string $model = ProductBrand::class;

    protected static ?string $modelLabel = 'marca';

    protected static ?string $pluralModelLabel = 'marcas';

    protected static ?string $navigationLabel = 'Marcas';

    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bookmark';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Marca')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(120),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Ativa',
                                'inactive' => 'Inativa',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativa',
                        'inactive' => 'Inativa',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Abrir'),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductBrands::route('/'),
            'create' => CreateProductBrand::route('/create'),
            'edit' => EditProductBrand::route('/{record}/edit'),
        ];
    }
}
