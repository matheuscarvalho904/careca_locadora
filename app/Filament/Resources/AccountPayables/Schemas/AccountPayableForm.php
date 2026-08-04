<?php

namespace App\Filament\Resources\AccountPayables\Schemas;

use App\Models\BusinessPartner;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountPayableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identificação')
                    ->columns(4)
                    ->schema([
                        TextInput::make('number')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('supplier_id')
                            ->label('Fornecedor')
                            ->required()
                            ->searchable()
                            ->options(
                                fn (): array => BusinessPartner::query()
                                    ->whereJsonContains('roles', 'supplier')
                                    ->orderBy('trade_name')
                                    ->get()
                                    ->mapWithKeys(
                                        fn ($partner): array => [
                                            $partner->id => $partner->display_name,
                                        ]
                                    )
                                    ->all()
                            ),

                        TextInput::make('document_number')
                            ->label('Documento')
                            ->maxLength(80),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Rascunho',
                                'awaiting_approval' => 'Aguardando aprovação',
                                'approved' => 'Aprovado',
                                'rejected' => 'Reprovado',
                                'overdue' => 'Vencido',
                                'partially_paid' => 'Parcialmente pago',
                                'paid' => 'Pago',
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),

                Section::make('Origem e vencimentos')
                    ->columns(4)
                    ->schema([
                        Select::make('purchase_order_id')
                            ->label('Ordem de Compra')
                            ->relationship('purchaseOrder', 'number')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(),

                        Select::make('purchase_receipt_id')
                            ->label('Recebimento')
                            ->relationship('purchaseReceipt', 'number')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('installment_number')
                            ->label('Parcela')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('installment_count')
                            ->label('Total de parcelas')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        DatePicker::make('issued_at')
                            ->label('Emissão'),

                        DatePicker::make('competence_date')
                            ->label('Competência'),

                        DatePicker::make('due_at')
                            ->label('Vencimento')
                            ->required(),
                    ]),

                Section::make('Valores')
                    ->columns(5)
                    ->schema([
                        TextInput::make('original_value')
                            ->label('Valor original')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->default(0),

                        TextInput::make('interest_value')
                            ->label('Juros')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('penalty_value')
                            ->label('Multa')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('discount_value')
                            ->label('Desconto')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('open_value')
                            ->label('Em aberto')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(),
                    ]),

                Section::make('Pagamento do fornecedor')
                    ->columns(2)
                    ->schema([
                        Select::make('bank_account_id')
                            ->label('Conta bancária do fornecedor')
                            ->relationship('bankAccount', 'description')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string => $record->display_name
                            ),

                        FileUpload::make('attachment_path')
                            ->label('Anexo')
                            ->directory('accounts-payable/attachments')
                            ->visibility('private'),

                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
