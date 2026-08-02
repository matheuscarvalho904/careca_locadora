<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\BusinessPartner;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $modelLabel = 'produto';
    protected static ?string $pluralModelLabel = 'produtos';
    protected static ?string $navigationLabel = 'Produtos';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Cadastro do produto')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Geral')
                            ->icon('heroicon-o-identification')
                            ->columns(3)
                            ->schema([
                                TextInput::make('code')->label('Código interno')->disabled()->dehydrated(false),
                                TextInput::make('sku')->label('SKU')->maxLength(80),
                                TextInput::make('barcode')->label('Código de barras')->maxLength(80),
                                TextInput::make('name')->label('Nome')->required()->maxLength(180),
                                TextInput::make('short_name')->label('Nome reduzido')->maxLength(100),
                                TextInput::make('manufacturer_code')->label('Código do fabricante')->maxLength(100),
                                Select::make('product_type')->label('Tipo')->required()->options([
                                    'product' => 'Produto',
                                    'consumable' => 'Material de consumo',
                                    'part' => 'Peça',
                                    'fuel' => 'Combustível',
                                    'lubricant' => 'Lubrificante',
                                    'tire' => 'Pneu',
                                    'tool' => 'Ferramenta',
                                    'ppe' => 'EPI',
                                    'electrical' => 'Material elétrico',
                                    'hydraulic' => 'Material hidráulico',
                                    'building' => 'Material predial',
                                    'office' => 'Material de escritório',
                                    'cleaning' => 'Material de limpeza',
                                ])->default('product'),
                                Select::make('category_id')->label('Categoria')->relationship('category', 'name')->searchable()->preload(),
                                Select::make('brand_id')->label('Marca')->relationship('brand', 'name')->searchable()->preload(),
                                Select::make('unit_id')->label('Unidade')->relationship('unit', 'symbol')->searchable()->preload(),
                                Select::make('status')->label('Status')->required()->options([
                                    'active' => 'Ativo',
                                    'inactive' => 'Inativo',
                                ])->default('active'),
                                Textarea::make('description')->label('Descrição técnica')->rows(4)->columnSpanFull(),
                            ]),
                        Tab::make('Estoque')
                            ->icon('heroicon-o-archive-box')
                            ->columns(3)
                            ->schema([
                                Toggle::make('stock_controlled')->label('Controla estoque')->default(true),
                                Toggle::make('allow_negative_stock')->label('Permite estoque negativo'),
                                Toggle::make('batch_controlled')->label('Controla lote'),
                                Toggle::make('expiry_controlled')->label('Controla validade'),
                                Toggle::make('serial_controlled')->label('Controla número de série'),
                                Toggle::make('asset_controlled')->label('Controla patrimônio'),
                                TextInput::make('minimum_stock')->label('Estoque mínimo')->numeric()->default(0),
                                TextInput::make('maximum_stock')->label('Estoque máximo')->numeric()->default(0),
                                TextInput::make('reorder_point')->label('Ponto de reposição')->numeric()->default(0),
                                Select::make('default_warehouse_id')->label('Almoxarifado padrão')->relationship('defaultWarehouse', 'name')->searchable()->preload(),
                            ]),
                        Tab::make('Compras e custos')
                            ->icon('heroicon-o-shopping-cart')
                            ->columns(3)
                            ->schema([
                                Select::make('primary_supplier_id')
                                    ->label('Fornecedor principal')
                                    ->searchable()
                                    ->options(fn (): array => BusinessPartner::query()
                                        ->whereJsonContains('roles', 'supplier')
                                        ->get()
                                        ->mapWithKeys(fn ($partner): array => [
                                            $partner->id => $partner->display_name,
                                        ])
                                        ->all()),
                                TextInput::make('minimum_purchase_quantity')->label('Quantidade mínima de compra')->numeric()->default(0),
                                TextInput::make('purchase_multiple')->label('Múltiplo de compra')->numeric()->default(1),
                                TextInput::make('lead_time_days')->label('Prazo médio em dias')->numeric()->default(0),
                                TextInput::make('average_cost')->label('Custo médio')->numeric()->prefix('R$')->default(0),
                                TextInput::make('last_purchase_cost')->label('Último custo')->numeric()->prefix('R$')->default(0),
                                TextInput::make('target_cost')->label('Custo-alvo')->numeric()->prefix('R$')->default(0),
                            ]),
                        Tab::make('Aplicação e financeiro')
                            ->icon('heroicon-o-map-pin')
                            ->columns(3)
                            ->schema([
                                Select::make('default_application_center_id')->label('Centro de aplicação padrão')->relationship('defaultApplicationCenter', 'name')->searchable()->preload(),
                                Select::make('default_cost_center_id')->label('Centro de custo padrão')->relationship('defaultCostCenter', 'name')->searchable()->preload(),
                                TextInput::make('financial_category')->label('Categoria financeira')->maxLength(120),
                                TextInput::make('accounting_account')->label('Conta contábil')->maxLength(80),
                                TextInput::make('economic_result')->label('Resultado econômico')->maxLength(120),
                            ]),
                        Tab::make('Fiscal')
                            ->icon('heroicon-o-document-text')
                            ->columns(3)
                            ->schema([
                                TextInput::make('ncm')->label('NCM')->maxLength(20),
                                TextInput::make('cest')->label('CEST')->maxLength(20),
                                Select::make('origin_code')->label('Origem da mercadoria')->options([
                                    '0' => '0 - Nacional',
                                    '1' => '1 - Estrangeira, importação direta',
                                    '2' => '2 - Estrangeira, adquirida no mercado interno',
                                    '3' => '3 - Nacional com conteúdo de importação superior a 40%',
                                    '4' => '4 - Nacional conforme processos produtivos básicos',
                                    '5' => '5 - Nacional com conteúdo de importação até 40%',
                                    '6' => '6 - Estrangeira, importação direta sem similar nacional',
                                    '7' => '7 - Estrangeira, mercado interno sem similar nacional',
                                    '8' => '8 - Nacional com conteúdo de importação superior a 70%',
                                ]),
                            ]),
                        Tab::make('Arquivos e observações')
                            ->icon('heroicon-o-paper-clip')
                            ->columns(2)
                            ->schema([
                                FileUpload::make('image_path')->label('Imagem do produto')->image()->directory('products/images'),
                                FileUpload::make('technical_sheet_path')->label('Ficha técnica')->directory('products/technical-sheets')->acceptedFileTypes([
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png',
                                ]),
                                Textarea::make('notes')->label('Observações internas')->rows(5)->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->weight('bold')
                    ->width('145px')
                    ->extraAttributes([
                        'class' => 'whitespace-nowrap',
                    ]),

                TextColumn::make('name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->limit(42)
                    ->tooltip(fn (Product $record): string => $record->name)
                    ->width('340px')
                    ->extraAttributes([
                        'class' => 'whitespace-nowrap',
                    ]),

                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->placeholder('—')
                    ->limit(22)
                    ->tooltip(fn (Product $record): ?string => $record->category?->name)
                    ->width('180px')
                    ->extraAttributes([
                        'class' => 'whitespace-nowrap',
                    ]),

                TextColumn::make('brand.name')
                    ->label('Marca')
                    ->placeholder('—')
                    ->limit(18)
                    ->width('130px')
                    ->extraAttributes([
                        'class' => 'whitespace-nowrap',
                    ]),

                TextColumn::make('unit.symbol')
                    ->label('Unidade')
                    ->placeholder('—')
                    ->width('90px')
                    ->extraAttributes([
                        'class' => 'whitespace-nowrap',
                    ]),

                TextColumn::make('product_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'product' => 'Produto',
                        'consumable' => 'Consumo',
                        'part' => 'Peça',
                        'fuel' => 'Combustível',
                        'lubricant' => 'Lubrificante',
                        'tire' => 'Pneu',
                        'tool' => 'Ferramenta',
                        'ppe' => 'EPI',
                        'electrical' => 'Elétrico',
                        'hydraulic' => 'Hidráulico',
                        'building' => 'Predial',
                        'office' => 'Escritório',
                        'cleaning' => 'Limpeza',
                        default => $state,
                    })
                    ->width('130px'),

                IconColumn::make('stock_controlled')
                    ->label('Estoque')
                    ->boolean()
                    ->width('90px'),

                TextColumn::make('minimum_stock')
                    ->label('Mínimo')
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd()
                    ->width('100px')
                    ->extraAttributes([
                        'class' => 'whitespace-nowrap',
                    ]),

                TextColumn::make('average_cost')
                    ->label('Custo médio')
                    ->money('BRL')
                    ->alignEnd()
                    ->width('130px')
                    ->extraAttributes([
                        'class' => 'whitespace-nowrap',
                    ]),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        $state === 'active'
                            ? 'Ativo'
                            : 'Inativo'
                    )
                    ->color(fn (string $state): string =>
                        $state === 'active'
                            ? 'success'
                            : 'gray'
                    )
                    ->width('100px'),
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
