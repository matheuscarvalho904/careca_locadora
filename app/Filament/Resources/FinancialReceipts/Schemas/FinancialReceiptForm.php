<?php

namespace App\Filament\Resources\FinancialReceipts\Schemas;

use App\Support\UI\PremiumFormLayout;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FinancialReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Recebimento')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('number')->label('Número')->disabled(),
                        Placeholder::make('receivable')->label('Conta a receber')
                            ->content(fn ($record) => $record?->receivable?->number ?? '—'),
                        Placeholder::make('customer')->label('Cliente')
                            ->content(fn ($record) => $record?->customer?->display_name ?? '—'),
                        Placeholder::make('received_at_display')->label('Data')
                            ->content(fn ($record) => $record?->received_at?->format('d/m/Y H:i') ?? '—'),
                        TextInput::make('payment_method')->label('Forma')->disabled(),
                        TextInput::make('principal_value')->label('Principal')->prefix('R$')->disabled(),
                        TextInput::make('interest_value')->label('Juros')->prefix('R$')->disabled(),
                        TextInput::make('penalty_value')->label('Multa')->prefix('R$')->disabled(),
                        TextInput::make('discount_value')->label('Desconto')->prefix('R$')->disabled(),
                        TextInput::make('additional_value')->label('Acréscimo')->prefix('R$')->disabled(),
                        TextInput::make('total_received')->label('Total recebido')->prefix('R$')->disabled(),
                        TextInput::make('status')->label('Status')->disabled(),
                        Textarea::make('notes')->label('Observações')->disabled()->columnSpanFull(),
                        Textarea::make('reversal_reason')->label('Motivo do estorno')->disabled()->columnSpanFull(),
                    ]),
            ]);
    }
}
