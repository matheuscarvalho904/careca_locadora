<?php

namespace App\Filament\Resources\BankAccounts;

use App\Filament\Resources\BankAccounts\Pages\CreateBankAccount;
use App\Filament\Resources\BankAccounts\Pages\EditBankAccount;
use App\Filament\Resources\BankAccounts\Pages\ListBankAccounts;
use App\Models\BankAccount;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;
    protected static ?string $modelLabel = 'conta bancária';
    protected static ?string $pluralModelLabel = 'contas bancárias';
    protected static ?string $navigationLabel = 'Contas bancárias';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-library';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Conta bancária')->columns(4)->schema([
                Select::make('owner_type')
                    ->label('Vincular conta a')
                    ->options([
                        'company' => 'Empresa',
                        'branch' => 'Filial',
                        'business_partner' => 'Cliente/Fornecedor',
                    ])
                    ->required()
                    ->live(),

                Select::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'legal_name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => $get('owner_type') === 'company'),

                Select::make('branch_id')
                    ->label('Filial')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => $get('owner_type') === 'branch'),

                Select::make('business_partner_id')
                    ->label('Cliente/Fornecedor')
                    ->relationship('businessPartner', 'legal_name')
                    ->searchable(['legal_name', 'trade_name', 'document'])
                    ->preload()
                    ->visible(fn (Get $get): bool => $get('owner_type') === 'business_partner'),

                Select::make('bank_id')
                    ->label('Banco')
                    ->relationship('bank', 'name')
                    ->searchable(['code', 'name', 'short_name'])
                    ->preload()
                    ->getOptionLabelFromRecordUsing(
                        fn ($record): string => "{$record->code} - {$record->name}"
                    )
                    ->required(),

                TextInput::make('description')->label('Identificação')->maxLength(120),
                TextInput::make('agency')->label('Agência')->maxLength(30),
                TextInput::make('agency_digit')->label('Dígito da agência')->maxLength(10),
                TextInput::make('account_number')->label('Conta')->maxLength(40),
                TextInput::make('account_digit')->label('Dígito da conta')->maxLength(10),

                Select::make('account_type')->label('Tipo da conta')->options([
                    'checking' => 'Conta corrente',
                    'savings' => 'Conta poupança',
                    'payment' => 'Conta de pagamento',
                    'cash' => 'Caixa',
                    'other' => 'Outra',
                ])->default('checking')->required(),

                TextInput::make('holder_name')->label('Titular')->required()->maxLength(180),
                TextInput::make('holder_document')->label('CPF/CNPJ do titular')->maxLength(20),

                Select::make('pix_key_type')->label('Tipo da chave PIX')->options([
                    'document' => 'CPF/CNPJ',
                    'email' => 'E-mail',
                    'phone' => 'Telefone',
                    'random' => 'Chave aleatória',
                ]),
                TextInput::make('pix_key')->label('Chave PIX')->maxLength(255)->columnSpan(2),

                Toggle::make('is_primary')->label('Conta principal'),
                Toggle::make('use_for_payments')->label('Pagamentos'),
                Toggle::make('use_for_receipts')->label('Recebimentos'),
                Toggle::make('use_for_invoices')->label('Exibir em faturas'),
                Toggle::make('use_for_boleto')->label('Boletos'),

                Select::make('status')->label('Status')->options([
                    'active' => 'Ativa',
                    'inactive' => 'Inativa',
                ])->default('active')->required(),

                Textarea::make('notes')->label('Observações')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('bank.code')->label('Código'),
            TextColumn::make('bank.name')->label('Banco')->searchable(),
            TextColumn::make('holder_name')->label('Titular')->searchable(),
            TextColumn::make('agency')->label('Agência')->placeholder('—'),
            TextColumn::make('account_number')->label('Conta')->placeholder('—'),
            TextColumn::make('pix_key')->label('PIX')->limit(35)->placeholder('—'),
            IconColumn::make('is_primary')->label('Principal')->boolean(),
            IconColumn::make('use_for_invoices')->label('Faturas')->boolean(),
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
            'index' => ListBankAccounts::route('/'),
            'create' => CreateBankAccount::route('/create'),
            'edit' => EditBankAccount::route('/{record}/edit'),
        ];
    }
}
