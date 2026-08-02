<?php

namespace App\Filament\Resources\RentalDeliveries\Schemas;

use App\Support\UI\PremiumFormLayout;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalDeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. Identificação da entrega')
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
                                'draft' => 'Em preparação',
                                'completed' => 'Entregue',
                                'cancelled' => 'Cancelada',
                            ]),

                        DateTimePicker::make('scheduled_at')
                            ->label('Agendada para')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        DateTimePicker::make('delivered_at')
                            ->label('Entregue em')
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('2. Vistoria e medidores')
                    ->description('Confira cada ativo antes de realizar a entrega.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->collapsible()
                            ->columns(PremiumFormLayout::repeater())
                            ->schema([
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

                                TextInput::make('odometer')
                                    ->label('Hodômetro')
                                    ->numeric()
                                    ->suffix(' km'),

                                TextInput::make('hourmeter')
                                    ->label('Horímetro')
                                    ->numeric()
                                    ->suffix(' h'),

                                Select::make('fuel_level')
                                    ->label('Nível de combustível')
                                    ->options([
                                        'empty' => 'Vazio',
                                        'quarter' => '1/4',
                                        'half' => '1/2',
                                        'three_quarters' => '3/4',
                                        'full' => 'Cheio',
                                        'not_applicable' => 'Não se aplica',
                                    ]),

                                Toggle::make('body_ok')
                                    ->label('Lataria/estrutura OK')
                                    ->default(true),

                                Toggle::make('tires_ok')
                                    ->label('Pneus/rodagem OK')
                                    ->default(true),

                                Toggle::make('lights_ok')
                                    ->label('Iluminação OK')
                                    ->default(true),

                                Toggle::make('glass_ok')
                                    ->label('Vidros/espelhos OK')
                                    ->default(true),

                                Toggle::make('documents_ok')
                                    ->label('Documentos OK')
                                    ->default(true),

                                Toggle::make('accessories_ok')
                                    ->label('Acessórios OK')
                                    ->default(true),

                                Toggle::make('cleanliness_ok')
                                    ->label('Limpeza OK')
                                    ->default(true),

                                Toggle::make('primary_key_delivered')
                                    ->label('Chave principal')
                                    ->default(true),

                                Toggle::make('spare_key_delivered')
                                    ->label('Chave reserva'),

                                Toggle::make('manual_delivered')
                                    ->label('Manual entregue'),

                                Textarea::make('existing_damage_notes')
                                    ->label('Avarias preexistentes')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Textarea::make('accessories_notes')
                                    ->label('Acessórios e observações')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                FileUpload::make('photos')
                                    ->label('Fotos do ativo')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->directory('rental-deliveries/assets')
                                    ->visibility('private')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('3. Registro fotográfico geral')
                    ->schema([
                        FileUpload::make('photos')
                            ->label('Fotos gerais da entrega')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->directory('rental-deliveries/general')
                            ->visibility('private')
                            ->columnSpanFull(),
                    ]),

                Section::make('4. Responsáveis e assinaturas')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('customer_signer_name')
                            ->label('Recebedor/cliente')
                            ->required()
                            ->maxLength(180)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('employee_signer_name')
                            ->label('Responsável pela entrega')
                            ->maxLength(180)
                            ->default(fn (): ?string => auth()->user()?->name)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        FileUpload::make('customer_signature_path')
                            ->label('Assinatura do cliente')
                            ->image()
                            ->directory('rental-deliveries/signatures')
                            ->visibility('private')
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        FileUpload::make('employee_signature_path')
                            ->label('Assinatura do responsável')
                            ->image()
                            ->directory('rental-deliveries/signatures')
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
}
