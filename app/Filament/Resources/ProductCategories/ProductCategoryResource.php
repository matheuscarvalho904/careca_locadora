<?php

namespace App\Filament\Resources\ProductCategories;

use App\Filament\Resources\ProductCategories\Pages\CreateProductCategory;
use App\Filament\Resources\ProductCategories\Pages\EditProductCategory;
use App\Filament\Resources\ProductCategories\Pages\ListProductCategories;
use App\Models\ProductCategory;
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

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;
    protected static ?string $modelLabel = 'categoria de produto';
    protected static ?string $pluralModelLabel = 'categorias de produtos';
    protected static ?string $navigationLabel = 'Categorias de produtos';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Categoria')
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(40),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(150),

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
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        $state === 'active' ? 'Ativa' : 'Inativa'
                    )
                    ->color(fn (string $state): string =>
                        $state === 'active' ? 'success' : 'gray'
                    ),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductCategories::route('/'),
            'create' => CreateProductCategory::route('/create'),
            'edit' => EditProductCategory::route('/{record}/edit'),
        ];
    }
}
