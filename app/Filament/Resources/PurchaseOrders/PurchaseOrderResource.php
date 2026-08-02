<?php

namespace App\Filament\Resources\PurchaseOrders;

use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Support\ProcurementSelectOptions;
use App\Models\BusinessPartner;
use App\Models\PurchaseOrder;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $modelLabel = 'ordem de compra';
    protected static ?string $pluralModelLabel = 'ordens de compra';
    protected static ?string $navigationLabel = 'Ordens de compra';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Ordem de compra')->tabs([
                Tab::make('Identificação')->schema([
                    Section::make('Dados principais')->columns(3)->schema([
                        TextInput::make('number')->label('Número')->disabled()->dehydrated(false),

                        Select::make('origin_type')->label('Origem')->required()->options([
                            'request_quotation' => 'SC/Cotação',
                            'request_direct' => 'SC direta',
                            'direct' => 'Compra direta',
                            'emergency' => 'Emergencial',
                            'contract' => 'Contrato vigente',
                            'regularization' => 'Regularização',
                        ])->default('direct'),

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

                        DatePicker::make('issued_at')->label('Emissão')->default(today()),
                        DatePicker::make('expected_delivery_at')->label('Previsão de entrega'),

                        Select::make('status')->label('Status')->options([
                            'draft' => 'Rascunho',
                            'awaiting_approval' => 'Aguardando aprovação',
                            'approved' => 'Aprovada',
                            'sent' => 'Enviada',
                            'partially_received' => 'Recebida parcialmente',
                            'received' => 'Recebida',
                            'finished' => 'Finalizada',
                            'cancelled' => 'Cancelada',
                        ])->default('draft')->required(),

                        TextInput::make('delivery_location')
                            ->label('Local de entrega')
                            ->maxLength(180)
                            ->columnSpanFull(),
                    ]),
                ]),

                Tab::make('Produtos e aplicações')->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->label('Itens da OC')
                        ->schema([
                            Select::make('product_id')
                                ->label('Produto')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->relationship('product', 'name')
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record): string =>
                                        ProcurementSelectOptions::productLabel($record)
                                )
                                ->getSearchResultsUsing(
                                    fn (string $search): array =>
                                        ProcurementSelectOptions::productResults($search)
                                ),

                            Select::make('application_type')
                                ->label('Aplicação')
                                ->required()
                                ->options([
                                    'application_center' => 'Centro de aplicação',
                                    'asset' => 'Ativo',
                                    'stock' => 'Estoque',
                                    'direct_consumption' => 'Consumo direto',
                                ])
                                ->live(),

                            Select::make('application_center_id')
                                ->label('Centro de aplicação')
                                ->relationship('applicationCenter', 'name')
                                ->searchable(['code', 'name'])
                                ->preload()
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record): string =>
                                        ProcurementSelectOptions::applicationCenterLabel($record)
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'application_center'
                                ),

                            Select::make('asset_id')
                                ->label('Ativo')
                                ->relationship('asset', 'name')
                                ->searchable(['prefix', 'plate', 'name'])
                                ->preload()
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record): string =>
                                        ProcurementSelectOptions::assetLabel($record)
                                )
                                ->getSearchResultsUsing(
                                    fn (string $search): array =>
                                        ProcurementSelectOptions::assetResults($search)
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),

                            Select::make('meter_type')
                                ->label('Tipo de medidor')
                                ->options([
                                    'odometer' => 'Hodômetro',
                                    'hourmeter' => 'Horímetro',
                                ])
                                ->required(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),

                            TextInput::make('meter_reading')
                                ->label('Leitura atual')
                                ->numeric()
                                ->minValue(0)
                                ->suffix(fn ($get): ?string => match ($get('meter_type')) {
                                    'odometer' => 'km',
                                    'hourmeter' => 'h',
                                    default => null,
                                })
                                ->required(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),

                            \Filament\Forms\Components\DateTimePicker::make('meter_recorded_at')
                                ->label('Data e hora da leitura')
                                ->seconds(false)
                                ->default(now())
                                ->required(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),

                            Select::make('warehouse_id')
                                ->label('Estoque de destino')
                                ->relationship('warehouse', 'name')
                                ->searchable(['code', 'name'])
                                ->preload()
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record): string =>
                                        ProcurementSelectOptions::warehouseLabel($record)
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'stock'
                                ),

                            Select::make('cost_center_id')
                                ->label('Centro de custo')
                                ->relationship('costCenter', 'name')
                                ->searchable(['code', 'name'])
                                ->preload()
                                ->getOptionLabelFromRecordUsing(
                                    fn ($record): string =>
                                        ProcurementSelectOptions::costCenterLabel($record)
                                ),

                            TextInput::make('quantity')
                                ->label('Quantidade')
                                ->numeric()
                                ->required()
                                ->default(1),

                            TextInput::make('unit_value')
                                ->label('Valor unitário')
                                ->numeric()
                                ->prefix('R$')
                                ->required()
                                ->default(0),

                            TextInput::make('discount_value')
                                ->label('Desconto')
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

                Tab::make('Financeiro')->schema([
                    Section::make('Condição de pagamento')->columns(3)->schema([
                        Select::make('payment_method')
                            ->label('Forma de pagamento')
                            ->options([
                                'pix' => 'PIX',
                                'bank_slip' => 'Boleto',
                                'bank_transfer' => 'Transferência bancária',
                                'cash' => 'Dinheiro',
                                'credit_card' => 'Cartão de crédito',
                                'debit_card' => 'Cartão de débito',
                                'check' => 'Cheque',
                                'other' => 'Outra',
                            ]),

                        Select::make('payment_condition_id')
                            ->label('Condição de pagamento')
                            ->relationship('paymentCondition', 'name')
                            ->searchable(['code', 'name'])
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string =>
                                    "{$record->code} - {$record->name}"
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, $set): void {
                                $condition = \App\Models\PaymentCondition::find($state);

                                if (! $condition) {
                                    return;
                                }

                                $set('installments', $condition->installments);
                                $set('installment_interval_days', $condition->interval_days);
                                $set(
                                    'first_due_date',
                                    now()->addDays($condition->first_due_days)->toDateString()
                                );
                            }),

                        DatePicker::make('first_due_date')
                            ->label('Primeiro vencimento'),

                        TextInput::make('installments')
                            ->label('Parcelas')
                            ->numeric()
                            ->minValue(1)
                            ->default(1),

                        TextInput::make('installment_interval_days')
                            ->label('Intervalo entre parcelas')
                            ->numeric()
                            ->suffix('dias')
                            ->minValue(0)
                            ->default(30),

                        TextInput::make('discount_value')
                            ->label('Desconto geral')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('freight_value')
                            ->label('Frete')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('additional_value')
                            ->label('Outras despesas')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),
                    ]),
                ]),

                Tab::make('Observações')->schema([
                    Section::make('Comunicação')->columns(2)->schema([
                        Textarea::make('supplier_notes')
                            ->label('Observações ao fornecedor')
                            ->rows(5),

                        Textarea::make('internal_notes')
                            ->label('Observações internas')
                            ->rows(5),
                    ]),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('OC')->searchable()->weight('bold'),
            TextColumn::make('supplier.display_name')->label('Fornecedor')->wrap(),
            TextColumn::make('issued_at')->label('Emissão')->date('d/m/Y')->sortable(),
            TextColumn::make('first_due_date')->label('Vencimento')->date('d/m/Y')->placeholder('—'),
            TextColumn::make('paymentCondition.name')->label('Condição')->placeholder('—'),
            TextColumn::make('total_value')->label('Total')->money('BRL')->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Rascunho',
                    'awaiting_approval' => 'Aguardando aprovação',
                    'approved' => 'Aprovada',
                    'sent' => 'Enviada',
                    'partially_received' => 'Recebida parcialmente',
                    'received' => 'Recebida',
                    'finished' => 'Finalizada',
                    'cancelled' => 'Cancelada',
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'awaiting_approval' => 'warning',
                    'approved', 'received', 'finished' => 'success',
                    'sent', 'partially_received' => 'info',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'edit' => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
