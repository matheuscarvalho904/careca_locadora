<?php

namespace App\Filament\Resources\RentalRatePlans;

use App\Filament\Resources\RentalRatePlans\Pages\CreateRentalRatePlan;
use App\Filament\Resources\RentalRatePlans\Pages\EditRentalRatePlan;
use App\Filament\Resources\RentalRatePlans\Pages\ListRentalRatePlans;
use App\Models\RentalRatePlan;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RentalRatePlanResource extends Resource
{
    protected static ?string $model = RentalRatePlan::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'tarifa';
    protected static ?string $pluralModelLabel = 'tarifas';
    protected static ?string $navigationLabel = 'Tarifas';
    protected static string | UnitEnum | null $navigationGroup = 'Locações';
    protected static string | BackedEnum | null $navigationIcon =
        'heroicon-o-banknotes';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Identificação da tarifa')
                ->columns(3)
                ->schema([
                    TextInput::make('code')
                        ->label('Código')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Gerado automaticamente.'),

                    TextInput::make('name')
                        ->label('Nome da tarifa')
                        ->required()
                        ->maxLength(150),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Ativa',
                            'inactive' => 'Inativa',
                        ])
                        ->default('active')
                        ->required()
                        ->native(false),

                    Select::make('asset_category_id')
                        ->label('Categoria')
                        ->relationship(
                            name: 'assetCategory',
                            titleAttribute: 'name',
                            modifyQueryUsing:
                                fn (Builder $query): Builder =>
                                    $query
                                        ->where('status', 'active')
                                        ->orderBy('name')
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('branch_id')
                        ->label('Filial')
                        ->relationship(
                            name: 'branch',
                            titleAttribute: 'name',
                            modifyQueryUsing:
                                fn (Builder $query): Builder =>
                                    $query
                                        ->where('status', 'active')
                                        ->orderBy('name')
                        )
                        ->placeholder('Todas as filiais')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('billing_unit')
                        ->label('Modalidade')
                        ->options([
                            'hourly' => 'Por hora',
                            'daily' => 'Diária',
                            'weekly' => 'Semanal',
                            'monthly' => 'Mensal',
                            'fixed' => 'Período fechado',
                        ])
                        ->default('daily')
                        ->required()
                        ->live()
                        ->native(false),
                ]),

            Section::make('Valores e regras')
                ->columns(4)
                ->schema([
                    TextInput::make('unit_value')
                        ->label('Valor')
                        ->numeric()
                        ->prefix('R$')
                        ->required(),

                    TextInput::make('deposit_value')
                        ->label('Caução')
                        ->numeric()
                        ->prefix('R$')
                        ->default(0)
                        ->required(),

                    TextInput::make('minimum_quantity')
                        ->label('Quantidade mínima')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->helperText(
                            'Use 15 em uma tarifa fechada de 15 dias.'
                        ),

                    TextInput::make('priority')
                        ->label('Prioridade')
                        ->numeric()
                        ->default(100)
                        ->required()
                        ->helperText('Menor número = maior prioridade.'),

                    TextInput::make('included_distance')
                        ->label('KM incluídos')
                        ->numeric()
                        ->suffix(' km'),

                    TextInput::make('extra_distance_value')
                        ->label('KM excedente')
                        ->numeric()
                        ->prefix('R$')
                        ->default(0)
                        ->required(),

                    TextInput::make('included_hours')
                        ->label('Horas incluídas')
                        ->numeric()
                        ->suffix(' h'),

                    TextInput::make('extra_hour_value')
                        ->label('Hora excedente')
                        ->numeric()
                        ->prefix('R$')
                        ->default(0)
                        ->required(),

DatePicker::make('valid_from')
                        ->label('Válida a partir de')
                        ->native(false),

                    DatePicker::make('valid_until')
                        ->label('Válida até')
                        ->native(false),
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
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Tarifa')
                    ->searchable()
                    ->sortable()
                    ->limit(35),

                TextColumn::make('assetCategory.name')
                    ->label('Categoria')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label('Filial')
                    ->placeholder('Todas')
                    ->badge()
                    ->sortable(),

                TextColumn::make('billing_unit')
                    ->label('Modalidade')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'hourly' => 'Hora',
                            'daily' => 'Diária',
                            'weekly' => 'Semanal',
                            'monthly' => 'Mensal',
                            'fixed' => 'Fechada',
                            default => $state,
                        }
                    ),

                TextColumn::make('minimum_quantity')
                    ->label('Mínimo')
                    ->numeric(),

                TextColumn::make('unit_value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('deposit_value')
                    ->label('Caução')
                    ->money('BRL')
                    ->toggleable(),

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
                SelectFilter::make('asset_category_id')
                    ->label('Categoria')
                    ->relationship('assetCategory', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('branch_id')
                    ->label('Filial')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('billing_unit')
                    ->label('Modalidade')
                    ->options([
                        'hourly' => 'Hora',
                        'daily' => 'Diária',
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensal',
                        'fixed' => 'Período fechado',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Abrir'),
            ])
            ->defaultSort('priority');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalRatePlans::route('/'),
            'create' => CreateRentalRatePlan::route('/create'),
            'edit' => EditRentalRatePlan::route('/{record}/edit'),
        ];
    }
}
