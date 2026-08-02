<?php

namespace App\Filament\Resources\RentalInvoices\Schemas;

use App\Support\UI\PremiumFormLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. Identificação da fatura de locação')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('number')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(false),

                        Placeholder::make('contract_display')
                            ->label('Contrato')
                            ->content(fn ($record): string =>
                                $record?->contract?->number ?? 'Não informado'
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

                        Select::make('status')
                            ->label('Status')
                            ->disabled()
                            ->dehydrated(false)
                            ->options([
                                'draft' => 'Rascunho',
                                'issued' => 'Emitida',
                                'partially_paid' => 'Parcialmente recebida',
                                'paid' => 'Recebida',
                                'cancelled' => 'Cancelada',
                            ]),

                        DatePicker::make('issued_at')
                            ->label('Emissão')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('due_at')
                            ->label('Primeiro vencimento')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),

                        DatePicker::make('competence_date')
                            ->label('Competência')
                            ->native(false)
                            ->displayFormat('m/Y')
                            ->required(),
                    ]),

                Section::make('2. Itens da fatura')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsible()
                            ->columns(PremiumFormLayout::repeater())
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->options([
                                        'rental' => 'Locação',
                                        'extra_time' => 'Tempo excedente',
                                        'mileage' => 'KM excedente',
                                        'fuel' => 'Combustível',
                                        'damage' => 'Avarias',
                                        'cleaning' => 'Limpeza',
                                        'missing_accessories' => 'Itens faltantes',
                                        'other' => 'Outros',
                                    ]),

                                TextInput::make('description')
                                    ->label('Descrição')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                TextInput::make('quantity')
                                    ->label('Quantidade')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('unit_value')
                                    ->label('Valor unitário')
                                    ->prefix('R$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('total_value')
                                    ->label('Total')
                                    ->prefix('R$')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('3. Valores')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('discount_value')
                            ->label('Desconto')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('additional_value')
                            ->label('Acréscimo manual')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('total_value')
                            ->label('Total')
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('received_value')
                            ->label('Recebido')
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('open_value')
                            ->label('Em aberto')
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
