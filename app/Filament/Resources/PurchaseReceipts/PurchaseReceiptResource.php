<?php
namespace App\Filament\Resources\PurchaseReceipts;

use App\Filament\Resources\PurchaseReceipts\Pages\CreatePurchaseReceipt;
use App\Filament\Resources\PurchaseReceipts\Pages\EditPurchaseReceipt;
use App\Filament\Resources\PurchaseReceipts\Pages\ListPurchaseReceipts;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PurchaseReceiptResource extends Resource
{
    protected static ?string $model = PurchaseReceipt::class;
    protected static ?string $modelLabel = 'recebimento de mercadoria';
    protected static ?string $pluralModelLabel = 'recebimentos de mercadorias';
    protected static ?string $navigationLabel = 'Recebimentos';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('organization_id')
                ->default(fn (): ?string => auth()->user()?->organization_id)
                ->dehydrated()
                ->required(),
            Hidden::make('supplier_id'),
            Hidden::make('received_by')->default(fn (): ?string => auth()->id()),

            Section::make('Identificação do recebimento')->columns(4)->schema([
                TextInput::make('number')->label('Número')->disabled()->dehydrated(false),

                Select::make('purchase_order_id')
                    ->label('Ordem de Compra')
                    ->relationship('purchaseOrder', 'number')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (blank($state)) {
                            $set('supplier_id', null);
                            $set('items', []);
                            return;
                        }

                        $order = PurchaseOrder::query()
                            ->with(['items.product'])
                            ->find($state);

                        if (! $order) {
                            return;
                        }

                        $set('supplier_id', $order->supplier_id);
                        $set('items', $order->items
                            ->filter(fn ($item): bool =>
                                (float) $item->received_quantity < (float) $item->quantity
                            )
                            ->map(fn ($item): array => [
                                'organization_id' => $order->organization_id,
                                'purchase_order_item_id' => $item->id,
                                'product_id' => $item->product_id,
                                'warehouse_id' => $item->warehouse_id,
                                'ordered_quantity' => $item->quantity,
                                'previous_received_quantity' => $item->received_quantity,
                                'received_quantity' => max(
                                    0,
                                    (float) $item->quantity - (float) $item->received_quantity
                                ),
                                'pending_quantity' => 0,
                                'unit_value' => $item->unit_value,
                                'discount_value' => 0,
                                'accepted' => true,
                            ])->values()->all());
                    }),

                Select::make('warehouse_id')
                    ->label('Almoxarifado padrão')
                    ->relationship('warehouse', 'name')
                    ->searchable(['code', 'name'])
                    ->preload(),

                DateTimePicker::make('received_at')
                    ->label('Data e hora')
                    ->seconds(false)
                    ->default(now())
                    ->required(),

                TextInput::make('invoice_number')->label('Número da NF')->maxLength(80),
                TextInput::make('invoice_series')->label('Série')->maxLength(30),
                DatePicker::make('invoice_issued_at')->label('Emissão da NF'),
                TextInput::make('invoice_access_key')->label('Chave de acesso')->maxLength(60)->columnSpanFull(),

                FileUpload::make('xml_path')
                    ->label('XML')
                    ->directory('purchase-receipts/xml')
                    ->visibility('private')
                    ->acceptedFileTypes(['application/xml', 'text/xml']),

                FileUpload::make('attachment_path')
                    ->label('Anexo')
                    ->directory('purchase-receipts/attachments')
                    ->visibility('private'),

                Textarea::make('notes')->label('Observações')->rows(3)->columnSpanFull(),
            ]),

            Section::make('Itens do recebimento')
                ->description('Informe somente a quantidade efetivamente recebida agora.')
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->columns(4)
                        ->schema([
                            Hidden::make('organization_id'),
                            Hidden::make('purchase_order_item_id'),
                            Hidden::make('product_id'),

                            Select::make('product_id')
                                ->label('Produto')
                                ->relationship('product', 'name')
                                ->disabled()
                                ->dehydrated()
                                ->searchable(),

                            TextInput::make('ordered_quantity')
                                ->label('Quantidade pedida')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('previous_received_quantity')
                                ->label('Recebido anteriormente')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('received_quantity')
                                ->label('Receber agora')
                                ->numeric()
                                ->minValue(0.0001)
                                ->required(),

                            Select::make('warehouse_id')
                                ->label('Almoxarifado')
                                ->relationship('warehouse', 'name')
                                ->searchable(['code', 'name'])
                                ->preload(),

                            TextInput::make('batch_number')->label('Lote')->maxLength(80),
                            DatePicker::make('expires_at')->label('Validade'),
                            TextInput::make('serial_number')->label('Número de série')->maxLength(120),

                            Select::make('divergence_type')
                                ->label('Divergência')
                                ->options([
                                    'quantity' => 'Quantidade',
                                    'price' => 'Preço',
                                    'quality' => 'Qualidade',
                                    'damaged' => 'Avaria',
                                    'other' => 'Outra',
                                ]),

                            Textarea::make('divergence_notes')
                                ->label('Detalhes da divergência')
                                ->rows(2)
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label('Observações do item')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),

            Section::make('Totais complementares')->columns(4)->schema([
                TextInput::make('discount_value')->label('Desconto')->numeric()->prefix('R$')->default(0),
                TextInput::make('freight_value')->label('Frete')->numeric()->prefix('R$')->default(0),
                TextInput::make('additional_value')->label('Outras despesas')->numeric()->prefix('R$')->default(0),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->default('draft')
                    ->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Recebimento')->searchable()->weight('bold'),
            TextColumn::make('purchaseOrder.number')->label('OC')->searchable(),
            TextColumn::make('supplier.display_name')->label('Fornecedor')->wrap(),
            TextColumn::make('invoice_number')->label('NF')->placeholder('—'),
            TextColumn::make('received_at')->label('Recebido em')->dateTime('d/m/Y H:i')->sortable(),
            TextColumn::make('total_value')->label('Total')->money('BRL'),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Rascunho',
                    'confirmed' => 'Confirmado',
                    'cancelled' => 'Cancelado',
                    default => $state,
                }),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseReceipts::route('/'),
            'create' => CreatePurchaseReceipt::route('/create'),
            'edit' => EditPurchaseReceipt::route('/{record}/edit'),
        ];
    }
}
