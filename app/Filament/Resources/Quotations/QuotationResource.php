<?php

namespace App\Filament\Resources\Quotations;

use App\Filament\Resources\Quotations\Pages\CreateQuotation;
use App\Filament\Resources\Quotations\Pages\EditQuotation;
use App\Filament\Resources\Quotations\Pages\ListQuotations;
use App\Filament\Support\ProcurementSelectOptions;
use App\Models\BusinessPartner;
use App\Models\Quotation;
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

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;
    protected static ?string $modelLabel = 'cotação';
    protected static ?string $pluralModelLabel = 'cotações';
    protected static ?string $navigationLabel = 'Cotações';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-scale';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('organization_id')
                ->default(fn (): ?string => auth()->user()?->organization_id)
                ->dehydrated()
                ->required(),

            Tabs::make('Cotação')->tabs([
                Tab::make('Identificação')->schema([
                    Section::make('Dados principais')->columns(3)->schema([
                        TextInput::make('number')->label('Número')->disabled()->dehydrated(false),

                        Select::make('purchase_request_id')
                            ->label('Solicitação de compra')
                            ->relationship('purchaseRequest', 'number')
                            ->searchable()
                            ->preload(),

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

                        Select::make('responsible_user_id')
                            ->label('Responsável')
                            ->relationship('responsibleUser', 'name')
                            ->default(fn (): ?string => auth()->id())
                            ->searchable()
                            ->preload(),

                        DatePicker::make('issued_at')
                            ->label('Data de emissão')
                            ->default(today())
                            ->required(),

                        DatePicker::make('response_deadline')
                            ->label('Prazo para resposta'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Rascunho',
                                'sent' => 'Enviada',
                                'collecting' => 'Recebendo propostas',
                                'analysis' => 'Em análise',
                                'selected' => 'Proposta selecionada',
                                'converted' => 'Convertida em OC',
                                'cancelled' => 'Cancelada',
                            ])
                            ->default('draft')
                            ->required(),

                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                ]),

                Tab::make('Produtos')->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->label('Itens da cotação')
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

                Tab::make('Fornecedores')->schema([
                    Repeater::make('suppliers')
                        ->relationship()
                        ->label('Fornecedores participantes')
                        ->schema([
                            Select::make('supplier_id')
                                ->label('Fornecedor')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(fn (): array => BusinessPartner::query()
                                    ->whereJsonContains('roles', 'supplier')
                                    ->orderBy('legal_name')
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(fn ($partner): array => [
                                        $partner->id => $partner->display_name,
                                    ])
                                    ->all()),

                            Select::make('payment_condition_id')
                                ->label('Condição de pagamento')
                                ->relationship('paymentCondition', 'name')
                                ->searchable(['code', 'name'])
                                ->preload(),

                            Select::make('status')
                                ->label('Situação')
                                ->options([
                                    'invited' => 'Convidado',
                                    'responded' => 'Respondeu',
                                    'declined' => 'Recusou',
                                    'selected' => 'Selecionado',
                                ])
                                ->default('invited')
                                ->required(),

                            DatePicker::make('proposal_date')->label('Data da proposta'),
                            DatePicker::make('proposal_valid_until')->label('Validade da proposta'),
                            TextInput::make('delivery_days')->label('Prazo de entrega')->numeric()->suffix('dias'),
                            TextInput::make('freight_value')->label('Frete')->numeric()->prefix('R$')->default(0),
                            TextInput::make('discount_value')->label('Desconto geral')->numeric()->prefix('R$')->default(0),
                            TextInput::make('additional_value')->label('Outras despesas')->numeric()->prefix('R$')->default(0),
                            Textarea::make('notes')->label('Observações')->rows(2)->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->defaultItems(2)
                        ->addActionLabel('Adicionar fornecedor')
                        ->reorderable(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Cotação')->searchable()->weight('bold'),
            TextColumn::make('purchaseRequest.number')->label('SC')->placeholder('—'),
            TextColumn::make('issued_at')->label('Emissão')->date('d/m/Y')->sortable(),
            TextColumn::make('response_deadline')->label('Prazo')->date('d/m/Y')->placeholder('—'),
            TextColumn::make('suppliers_count')->label('Fornecedores')->counts('suppliers'),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Rascunho',
                    'sent' => 'Enviada',
                    'collecting' => 'Recebendo propostas',
                    'analysis' => 'Em análise',
                    'selected' => 'Proposta selecionada',
                    'converted' => 'Convertida em OC',
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
            'index' => ListQuotations::route('/'),
            'create' => CreateQuotation::route('/create'),
            'edit' => EditQuotation::route('/{record}/edit'),
        ];
    }
}
