<?php

namespace App\Filament\Resources\AssetCategories;

use App\Filament\Resources\AssetCategories\Pages\CreateAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\EditAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\ListAssetCategories;
use App\Models\AssetCategory;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AssetCategoryResource extends Resource
{
    protected static ?string $model = AssetCategory::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'categoria de ativo';
    protected static ?string $pluralModelLabel = 'categorias de ativos';
    protected static ?string $navigationLabel = 'Categorias de ativos';
    protected static string | UnitEnum | null $navigationGroup = 'Frota';
    protected static string | BackedEnum | null $navigationIcon =
        'heroicon-o-squares-2x2';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identificação da categoria')
                    ->description(
                        'Classificação operacional utilizada em ativos, reservas e tarifas.'
                    )
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome da categoria')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('prefix')
                            ->label('Prefixo')
                            ->required()
                            ->maxLength(10)
                            ->helperText('Ex.: VL, CA, EH, MN.')
                            ->dehydrateStateUsing(
                                fn (?string $state): ?string =>
                                    filled($state)
                                        ? mb_strtoupper(trim($state))
                                        : null
                            ),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Ativa',
                                'inactive' => 'Inativa',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),

                        Select::make('asset_type')
                            ->label('Tipo de ativo')
                            ->options([
                                'vehicle' => 'Veículo',
                                'equipment' => 'Equipamento',
                                'machine' => 'Máquina',
                                'trailer' => 'Implemento/Reboque',
                                'other' => 'Outro',
                            ])
                            ->default('vehicle')
                            ->required()
                            ->native(false),

                        Select::make('meter_type')
                            ->label('Tipo de medidor')
                            ->options([
                                'odometer' => 'Hodômetro (km)',
                                'hourmeter' => 'Horímetro (h)',
                                'none' => 'Sem medidor',
                            ])
                            ->default('odometer')
                            ->required()
                            ->native(false),

                        TextInput::make('display_order')
                            ->label('Ordem de exibição')
                            ->numeric()
                            ->default(100)
                            ->required(),
                    ]),

                Section::make('Apresentação comercial')
                    ->description(
                        'Informações usadas no catálogo público por categoria.'
                    )
                    ->columns(2)
                    ->schema([
                        TextInput::make('metadata.public_title')
                            ->label('Título no site')
                            ->maxLength(150)
                            ->placeholder('Ex.: Caminhonete 4x4'),

                        TextInput::make('metadata.similar_models')
                            ->label('Modelos ou similares')
                            ->maxLength(250)
                            ->placeholder(
                                'Ex.: Toyota Hilux, S10, Ranger ou similar'
                            ),

                        Textarea::make(
                            'metadata.commercial_description'
                        )
                            ->label('Descrição comercial')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('metadata.cover_image')
                            ->label('Imagem de capa')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('public/catalog/categories')
                            ->visibility('public')
                            ->columnSpanFull(),

                        Toggle::make('metadata.featured')
                            ->label('Destacar no site')
                            ->default(false),
                    ]),

                Section::make('Regras documentais')
                    ->columns(3)
                    ->schema([
                        Toggle::make('requires_plate')
                            ->label('Exige placa')
                            ->default(true),

                        Toggle::make('requires_renavam')
                            ->label('Exige RENAVAM')
                            ->default(true),

                        Toggle::make('requires_chassis')
                            ->label('Exige chassi')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_order')
                    ->label('Ordem')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Categoria')
                    ->description(
                        fn (AssetCategory $record): ?string =>
                            data_get(
                                $record->metadata,
                                'similar_models'
                            )
                    )
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('prefix')
                    ->label('Prefixo')
                    ->badge()
                    ->searchable(),

                TextColumn::make('asset_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'vehicle' => 'Veículo',
                            'equipment' => 'Equipamento',
                            'machine' => 'Máquina',
                            'trailer' => 'Implemento/Reboque',
                            default => 'Outro',
                        }
                    ),

                IconColumn::make('metadata.featured')
                    ->label('Destaque')
                    ->boolean(),

                TextColumn::make('assets_count')
                    ->label('Ativos')
                    ->counts('assets'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string =>
                            $state === 'active'
                                ? 'Ativa'
                                : 'Inativa'
                    ),
            ])
            ->filters([
                SelectFilter::make('asset_type')
                    ->label('Tipo de ativo')
                    ->options([
                        'vehicle' => 'Veículo',
                        'equipment' => 'Equipamento',
                        'machine' => 'Máquina',
                        'trailer' => 'Implemento/Reboque',
                        'other' => 'Outro',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativa',
                        'inactive' => 'Inativa',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('display_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetCategories::route('/'),
            'create' => CreateAssetCategory::route('/create'),
            'edit' => EditAssetCategory::route('/{record}/edit'),
        ];
    }
}
