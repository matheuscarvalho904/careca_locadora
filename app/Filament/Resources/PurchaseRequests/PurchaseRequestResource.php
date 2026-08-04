<?php

namespace App\Filament\Resources\PurchaseRequests;

use App\Filament\Resources\PurchaseRequests\Pages\CreatePurchaseRequest;
use App\Filament\Resources\PurchaseRequests\Pages\EditPurchaseRequest;
use App\Filament\Resources\PurchaseRequests\Pages\ListPurchaseRequests;
use App\Filament\Support\ProcurementSelectOptions;
use App\Models\PurchaseRequest;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;
    protected static ?string $modelLabel = 'solicitação de compra';
    protected static ?string $pluralModelLabel = 'solicitações de compra';
    protected static ?string $navigationLabel = 'Solicitações de compra';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('organization_id')
                ->default(fn (): ?string => auth()->user()?->organization_id)
                ->dehydrated()
                ->required(),

            Tabs::make('Solicitação de compra')->tabs([
                Tab::make('Identificação')->schema([
                    Section::make('Dados principais')->columns(3)->schema([
                        TextInput::make('number')->label('Número')->disabled()->dehydrated(false),

                        Select::make('company_id')
                            ->label('Empresa')
                            ->relationship('company', 'legal_name')
                            ->searchable()
                            ->preload(),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('requester_id')
                            ->label('Solicitante')
                            ->relationship('requester', 'name')
                            ->default(fn (): ?string => auth()->id())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('department_id')
                            ->label('Departamento')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('cost_center_id')
                            ->label('Centro de custo')
                            ->relationship('costCenter', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('priority')
                            ->label('Prioridade')
                            ->options([
                                'low' => 'Baixa',
                                'normal' => 'Normal',
                                'high' => 'Alta',
                                'urgent' => 'Urgente',
                            ])
                            ->default('normal')
                            ->required(),

                        DatePicker::make('requested_at')
                            ->label('Data da solicitação')
                            ->default(today())
                            ->required(),

                        DatePicker::make('needed_at')
                            ->label('Necessário até'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Rascunho',
                                'awaiting_approval' => 'Aguardando aprovação',
                                'approved' => 'Aprovada',
                                'rejected' => 'Rejeitada',
                                'converted' => 'Convertida',
                                'cancelled' => 'Cancelada',
                            ])
                            ->default('draft')
                            ->required(),

                        Textarea::make('justification')
                            ->label('Justificativa')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('internal_notes')
                            ->label('Observações internas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                ]),

                Tab::make('Produtos e aplicações')->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->label('Itens da solicitação')
                        ->schema([
                            Select::make('product_id')
                                ->label('Produto')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record): string => ProcurementSelectOptions::productLabel($record)
                                )
                                ->getSearchResultsUsing(
                                    fn (string $search): array => ProcurementSelectOptions::productResults($search)
                                )
                                ->required(),

                            Select::make('application_type')
                                ->label('Aplicação')
                                ->options([
                                    'application_center' => 'Centro de aplicação',
                                    'asset' => 'Ativo',
                                    'stock' => 'Estoque',
                                    'direct_consumption' => 'Consumo interno',
                                ])
                                ->live()
                                ->required(),

                            Select::make('application_center_id')
                                ->label('Centro de aplicação')
                                ->relationship('applicationCenter', 'name')
                                ->searchable(['code', 'name'])
                                ->preload()
                                ->visible(fn (Get $get): bool => $get('application_type') === 'application_center'),

                            Select::make('asset_id')
                                ->label('Ativo')
                                ->relationship('asset', 'name')
                                ->searchable(['prefix', 'plate', 'name'])
                                ->preload()
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record): string => ProcurementSelectOptions::assetLabel($record)
                                )
                                ->visible(fn (Get $get): bool => $get('application_type') === 'asset'),

                            Select::make('meter_type')
                                ->label('Tipo de medidor')
                                ->options([
                                    'odometer' => 'Hodômetro',
                                    'hourmeter' => 'Horímetro',
                                ])
                                ->visible(fn (Get $get): bool => $get('application_type') === 'asset'),

                            TextInput::make('meter_reading')
                                ->label('Leitura atual')
                                ->numeric()
                                ->minValue(0)
                                ->visible(fn (Get $get): bool => $get('application_type') === 'asset'),

                            Select::make('warehouse_id')
                                ->label('Estoque de destino')
                                ->relationship('warehouse', 'name')
                                ->searchable(['code', 'name'])
                                ->preload()
                                ->visible(fn (Get $get): bool => $get('application_type') === 'stock'),

                            Select::make('cost_center_id')
                                ->label('Centro de custo')
                                ->relationship('costCenter', 'name')
                                ->searchable(['code', 'name'])
                                ->preload(),

                            TextInput::make('quantity')
                                ->label('Quantidade')
                                ->numeric()
                                ->minValue(0.0001)
                                ->default(1)
                                ->required(),

                            TextInput::make('estimated_unit_value')
                                ->label('Valor estimado')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0),

                            Textarea::make('notes')
                                ->label('Observações do item')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->addActionLabel('Adicionar produto')
                        ->reorderable(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('SC')->searchable()->weight('bold'),
            TextColumn::make('requested_at')->label('Data')->date('d/m/Y')->sortable(),
            TextColumn::make('requester.name')->label('Solicitante')->searchable(),
            TextColumn::make('priority')->label('Prioridade')->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'low' => 'Baixa',
                    'normal' => 'Normal',
                    'high' => 'Alta',
                    'urgent' => 'Urgente',
                    default => $state,
                }),
            TextColumn::make('estimated_total')->label('Estimativa')->money('BRL'),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Rascunho',
                    'awaiting_approval' => 'Aguardando aprovação',
                    'approved' => 'Aprovada',
                    'rejected' => 'Rejeitada',
                    'converted' => 'Convertida',
                    'cancelled' => 'Cancelada',
                    default => $state,
                }),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseRequests::route('/'),
            'create' => CreatePurchaseRequest::route('/create'),
            'edit' => EditPurchaseRequest::route('/{record}/edit'),
        ];
    }
}
