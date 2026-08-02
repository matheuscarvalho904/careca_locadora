<?php

namespace App\Filament\Resources\RentalReturns\Schemas;

use App\Support\UI\PremiumFormLayout;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. Identificação da devolução')
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
                                $record?->contract?->customer?->display_name
                                ?? 'Não informado'
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
                                'draft' => 'Em conferência',
                                'completed' => 'Devolvida',
                                'cancelled' => 'Cancelada',
                            ]),

                        DateTimePicker::make('scheduled_at')
                            ->label('Prevista para')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        DateTimePicker::make('returned_at')
                            ->label('Devolvida em')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('2. Comparação da entrega e devolução')
                    ->description('Registre os medidores, estado, combustível, fotos e cobranças de cada ativo.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->collapsible()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(PremiumFormLayout::repeater())
                            ->schema([
                                Hidden::make('organization_id'),
                                Hidden::make('delivery_item_id'),
                                Hidden::make('contract_item_id'),
                                Hidden::make('asset_id'),

                                Placeholder::make('asset_display')
                                    ->label('Ativo')
                                    ->content(fn ($record): string =>
                                        trim(
                                            ($record?->asset?->prefix ?? '')
                                            . ' — '
                                            . ($record?->asset?->name ?? 'Ativo')
                                        )
                                    )
                                    ->columnSpanFull(),

                                Placeholder::make('initial_odometer_display')
                                    ->label('KM na entrega')
                                    ->content(fn ($record): string =>
                                        $record?->initial_odometer !== null
                                            ? number_format((float) $record->initial_odometer, 2, ',', '.') . ' km'
                                            : 'Não se aplica'
                                    ),

                                TextInput::make('final_odometer')
                                    ->label('KM na devolução')
                                    ->numeric()
                                    ->suffix(' km'),

                                Placeholder::make('distance_used_display')
                                    ->label('KM utilizados')
                                    ->content(fn ($record): string =>
                                        $record?->distance_used !== null
                                            ? number_format((float) $record->distance_used, 2, ',', '.') . ' km'
                                            : 'Calculado ao salvar'
                                    ),

                                Placeholder::make('initial_hourmeter_display')
                                    ->label('Horímetro na entrega')
                                    ->content(fn ($record): string =>
                                        $record?->initial_hourmeter !== null
                                            ? number_format((float) $record->initial_hourmeter, 2, ',', '.') . ' h'
                                            : 'Não se aplica'
                                    ),

                                TextInput::make('final_hourmeter')
                                    ->label('Horímetro na devolução')
                                    ->numeric()
                                    ->suffix(' h'),

                                Placeholder::make('hours_used_display')
                                    ->label('Horas utilizadas')
                                    ->content(fn ($record): string =>
                                        $record?->hours_used !== null
                                            ? number_format((float) $record->hours_used, 2, ',', '.') . ' h'
                                            : 'Calculado ao salvar'
                                    ),

                                Placeholder::make('initial_fuel_display')
                                    ->label('Combustível na entrega')
                                    ->content(fn ($record): string => match ($record?->initial_fuel_level) {
                                        'empty' => 'Vazio',
                                        'quarter' => '1/4',
                                        'half' => '1/2',
                                        'three_quarters' => '3/4',
                                        'full' => 'Cheio',
                                        'not_applicable' => 'Não se aplica',
                                        default => 'Não informado',
                                    }),

                                Select::make('final_fuel_level')
                                    ->label('Combustível na devolução')
                                    ->options([
                                        'empty' => 'Vazio',
                                        'quarter' => '1/4',
                                        'half' => '1/2',
                                        'three_quarters' => '3/4',
                                        'full' => 'Cheio',
                                        'not_applicable' => 'Não se aplica',
                                    ]),

                                Toggle::make('body_ok')->label('Lataria/estrutura OK'),
                                Toggle::make('tires_ok')->label('Pneus/rodagem OK'),
                                Toggle::make('lights_ok')->label('Iluminação OK'),
                                Toggle::make('glass_ok')->label('Vidros/espelhos OK'),
                                Toggle::make('documents_ok')->label('Documentos OK'),
                                Toggle::make('accessories_ok')->label('Acessórios OK'),
                                Toggle::make('cleanliness_ok')->label('Limpeza OK'),
                                Toggle::make('primary_key_returned')->label('Chave principal devolvida'),
                                Toggle::make('spare_key_returned')->label('Chave reserva devolvida'),
                                Toggle::make('manual_returned')->label('Manual devolvido'),

                                Textarea::make('new_damage_notes')
                                    ->label('Novas avarias')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Textarea::make('missing_accessories_notes')
                                    ->label('Acessórios/documentos faltantes')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                FileUpload::make('photos')
                                    ->label('Fotos da devolução')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->directory('rental-returns/assets')
                                    ->visibility('private')
                                    ->columnSpanFull(),

                                TextInput::make('extra_time_value')->label('Tempo excedente')->numeric()->prefix('R$')->default(0),
                                TextInput::make('mileage_value')->label('KM excedente')->numeric()->prefix('R$')->default(0),
                                TextInput::make('fuel_value')->label('Combustível')->numeric()->prefix('R$')->default(0),
                                TextInput::make('damage_value')->label('Avarias')->numeric()->prefix('R$')->default(0),
                                TextInput::make('cleaning_value')->label('Lavagem/limpeza')->numeric()->prefix('R$')->default(0),
                                TextInput::make('missing_accessories_value')->label('Itens faltantes')->numeric()->prefix('R$')->default(0),
                                TextInput::make('other_value')->label('Outras cobranças')->numeric()->prefix('R$')->default(0),

                                Placeholder::make('item_total_display')
                                    ->label('Total adicional do ativo')
                                    ->content(fn ($record): string =>
                                        'R$ ' . number_format(
                                            (float) ($record?->total_charge_value ?? 0),
                                            2,
                                            ',',
                                            '.'
                                        )
                                    ),

                                Textarea::make('other_charge_notes')
                                    ->label('Justificativa de outras cobranças')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('3. Resumo das cobranças adicionais')
                    ->description('Valores consolidados de todos os ativos da devolução.')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        Placeholder::make('summary_extra_time')
                            ->label('Tempo excedente')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('extra_time_value') ?? 0
                            )),

                        Placeholder::make('summary_mileage')
                            ->label('KM excedente')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('mileage_value') ?? 0
                            )),

                        Placeholder::make('summary_fuel')
                            ->label('Combustível')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('fuel_value') ?? 0
                            )),

                        Placeholder::make('summary_damage')
                            ->label('Avarias')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('damage_value') ?? 0
                            )),

                        Placeholder::make('summary_cleaning')
                            ->label('Limpeza')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('cleaning_value') ?? 0
                            )),

                        Placeholder::make('summary_missing_accessories')
                            ->label('Itens faltantes')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('missing_accessories_value') ?? 0
                            )),

                        Placeholder::make('summary_other')
                            ->label('Outros')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('other_value') ?? 0
                            )),

                        Placeholder::make('summary_total')
                            ->label('Total adicional')
                            ->content(fn ($record): string => self::money(
                                $record?->items()->sum('total_charge_value') ?? 0
                            )),
                    ]),

                Section::make('4. Fotos, responsáveis e assinaturas')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        FileUpload::make('photos')
                            ->label('Fotos gerais da devolução')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->directory('rental-returns/general')
                            ->visibility('private')
                            ->columnSpanFull(),

                        TextInput::make('customer_signer_name')
                            ->label('Responsável pela devolução')
                            ->required()
                            ->maxLength(180)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('employee_signer_name')
                            ->label('Responsável pela conferência')
                            ->maxLength(180)
                            ->default(fn (): ?string => auth()->user()?->name)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        FileUpload::make('customer_signature_path')
                            ->label('Assinatura do cliente')
                            ->image()
                            ->directory('rental-returns/signatures')
                            ->visibility('private')
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        FileUpload::make('employee_signature_path')
                            ->label('Assinatura do responsável')
                            ->image()
                            ->directory('rental-returns/signatures')
                            ->visibility('private')
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        Textarea::make('general_notes')
                            ->label('Observações gerais')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function money(float|int|string|null $value): string
    {
        return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
