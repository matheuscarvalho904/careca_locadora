<?php

namespace App\Filament\Resources\FinancialAccounts\Schemas;

use App\Support\UI\PremiumFormLayout;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FinancialAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Conta financeira')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(180),

                        Select::make('type')
                            ->label('Tipo')
                            ->required()
                            ->options([
                                'cash' => 'Caixa',
                                'bank' => 'Conta bancária',
                                'digital' => 'Conta digital',
                            ])
                            ->default('bank'),

                        TextInput::make('bank_name')
                            ->label('Banco')
                            ->maxLength(120),

                        TextInput::make('agency')
                            ->label('Agência')
                            ->maxLength(40),

                        TextInput::make('account_number')
                            ->label('Conta')
                            ->maxLength(60),

                        TextInput::make('pix_key')
                            ->label('Chave PIX')
                            ->maxLength(180),

                        TextInput::make('opening_balance')
                            ->label('Saldo inicial')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        Toggle::make('is_default')
                            ->label('Conta padrão'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Ativa',
                                'inactive' => 'Inativa',
                            ])
                            ->default('active'),

                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
