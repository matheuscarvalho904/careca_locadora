<?php

namespace App\Filament\Resources\AccountsReceivable\Schemas;

use App\Support\UI\PremiumFormLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountReceivableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. Identificação')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('number')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(false),

                        Placeholder::make('invoice_display')
                            ->label('Fatura')
                            ->content(fn ($record): string =>
                                $record?->invoice?->number ?? 'Não informado'
                            ),

                        Placeholder::make('customer_display')
                            ->label('Cliente')
                            ->content(fn ($record): string =>
                                $record?->customer?->display_name ?? 'Não informado'
                            )
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('installment_number')
                            ->label('Parcela')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('installments_count')
                            ->label('Total de parcelas')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('status')
                            ->label('Status')
                            ->disabled()
                            ->dehydrated(false)
                            ->options([
                                'open' => 'Em aberto',
                                'overdue' => 'Vencida',
                                'partially_paid' => 'Parcialmente recebida',
                                'paid' => 'Recebida',
                                'cancelled' => 'Cancelada',
                            ]),

                        DatePicker::make('issued_at')
                            ->label('Emissão')
                            ->disabled()
                            ->dehydrated(false),

                        DatePicker::make('due_at')
                            ->label('Vencimento')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DateTimePicker::make('paid_at')
                            ->label('Recebida em')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('2. Valores e recebimento')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('original_value')
                            ->label('Valor original')
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(false),

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

                        TextInput::make('paid_value')
                            ->label('Valor recebido')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('open_value')
                            ->label('Saldo em aberto')
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('payment_method')
                            ->label('Forma de recebimento')
                            ->options([
                                'pix' => 'PIX',
                                'transfer' => 'Transferência',
                                'cash' => 'Dinheiro',
                                'card' => 'Cartão',
                                'boleto' => 'Boleto',
                                'other' => 'Outro',
                            ]),

                        TextInput::make('payment_reference')
                            ->label('Referência do pagamento')
                            ->maxLength(180),

                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
