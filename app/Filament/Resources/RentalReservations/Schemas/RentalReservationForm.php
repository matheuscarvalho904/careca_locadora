<?php

namespace App\Filament\Resources\RentalReservations\Schemas;

use App\Services\Rentals\RentalAvailabilityService;

use App\Models\Asset;
use App\Models\Branch;
use App\Models\BusinessPartner;
use App\Models\BusinessPartnerContact;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use App\Support\UI\PremiumFormLayout;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. Identificação da reserva')
                    ->description('Cliente, estrutura organizacional e situação.')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('number')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Gerado automaticamente'),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft' => 'Rascunho',
                                'pending' => 'Pendente',
                                'confirmed' => 'Confirmada',
                                'preparing' => 'Em preparação',
                                'converted' => 'Convertida em locação',
                                'completed' => 'Concluída',
                                'cancelled' => 'Cancelada',
                            ]),

                        Select::make('business_partner_id')
                            ->label('Cliente')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->options(fn (): array =>
                                BusinessPartner::query()
                                    ->whereJsonContains('roles', 'customer')
                                    ->where('status', 'active')
                                    ->orderBy('legal_name')
                                    ->get()
                                    ->mapWithKeys(fn (BusinessPartner $partner): array => [
                                        $partner->id => "{$partner->code} — {$partner->display_name}",
                                    ])
                                    ->all()
                            ),

                        Select::make('authorized_contact_id')
                            ->label('Contato autorizado')
                            ->searchable()
                            ->options(fn (callable $get): array =>
                                BusinessPartnerContact::query()
                                    ->where('business_partner_id', $get('business_partner_id'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()
                            ),

                        Select::make('company_id')
                            ->label('Empresa')
                            ->searchable()
                            ->preload()
                            ->options(fn (): array =>
                                Company::query()
                                    ->where('organization_id', auth()->user()?->organization_id)
                                    ->orderBy('trade_name')
                                    ->pluck('trade_name', 'id')
                                    ->all()
                            ),

                        Select::make('branch_id')
                            ->label('Filial')
                            ->searchable()
                            ->options(fn (callable $get): array =>
                                Branch::query()
                                    ->when(
                                        $get('company_id'),
                                        fn ($query, $companyId) => $query->where('company_id', $companyId)
                                    )
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()
                            ),

                        Select::make('cost_center_id')
                            ->label('Centro de custo')
                            ->searchable()
                            ->options(fn (): array =>
                                CostCenter::query()
                                    ->where('status', 'active')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()
                            ),

                        Select::make('responsible_user_id')
                            ->label('Responsável interno')
                            ->searchable()
                            ->options(fn (): array =>
                                User::query()
                                    ->where('organization_id', auth()->user()?->organization_id)
                                    ->where('status', 'active')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()
                            ),
                    ]),

                Section::make('2. Período e logística')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        DateTimePicker::make('pickup_expected_at')
                            ->label('Retirada prevista')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->live(),

                        DateTimePicker::make('return_expected_at')
                            ->label('Devolução prevista')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->after('pickup_expected_at')
                            ->live(),

                        TextInput::make('pickup_location')
                            ->label('Local de retirada')
                            ->maxLength(255)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('return_location')
                            ->label('Local de devolução')
                            ->maxLength(255)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),
                    ]),

                Section::make('3. Ativos reservados')
                    ->description('O sistema bloqueia automaticamente conflitos de período.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->minItems(1)
                            ->defaultItems(1)
                            ->collapsible()
                            ->columns(PremiumFormLayout::repeater())
                            ->mutateRelationshipDataBeforeCreateUsing(
                                fn (array $data, callable $get): array => array_merge($data, [
                                    'organization_id' => auth()->user()?->organization_id,
                                    'starts_at' => $data['starts_at'] ?? $get('../../pickup_expected_at'),
                                    'ends_at' => $data['ends_at'] ?? $get('../../return_expected_at'),
                                ])
                            )
                            ->schema([
                                Select::make('asset_id')
                                    ->label('Ativo disponível')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->options(function (callable $get, $record): array {
                                        $startsAt = $get('../../pickup_expected_at');
                                        $endsAt = $get('../../return_expected_at');

                                        if (blank($startsAt) || blank($endsAt)) {
                                            return [];
                                        }

                                        return app(RentalAvailabilityService::class)
                                            ->availableAssetOptions(
                                                organizationId: (string) (auth()->user()?->organization_id),
                                                startsAt: $startsAt,
                                                endsAt: $endsAt,
                                                ignoreReservationId: $record?->reservation_id,
                                            );
                                    })
                                    ->getSearchResultsUsing(function (string $search, callable $get, $record): array {
                                        $startsAt = $get('../../pickup_expected_at');
                                        $endsAt = $get('../../return_expected_at');

                                        if (blank($startsAt) || blank($endsAt)) {
                                            return [];
                                        }

                                        return app(RentalAvailabilityService::class)
                                            ->availableAssetOptions(
                                                organizationId: (string) (auth()->user()?->organization_id),
                                                startsAt: $startsAt,
                                                endsAt: $endsAt,
                                                ignoreReservationId: $record?->reservation_id,
                                                search: $search,
                                            );
                                    }),


                                Hidden::make('starts_at'),
                                Hidden::make('ends_at'),

                                Select::make('billing_unit')
                                    ->label('Unidade')
                                    ->required()
                                    ->default('daily')
                                    ->options([
                                        'hourly' => 'Hora',
                                        'daily' => 'Diária',
                                        'weekly' => 'Semanal',
                                        'monthly' => 'Mensal',
                                        'fixed' => 'Valor fechado',
                                    ]),

                                TextInput::make('quantity')
                                    ->label('Quantidade')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.001),

                                TextInput::make('unit_value')
                                    ->label('Valor unitário')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required()
                                    ->default(0),

                                TextInput::make('discount_value')
                                    ->label('Desconto')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0),

                                TextInput::make('additional_value')
                                    ->label('Acréscimo')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0),

                                TextInput::make('expected_initial_odometer')
                                    ->label('KM inicial previsto')
                                    ->numeric()
                                    ->suffix(' km'),

                                TextInput::make('expected_initial_hourmeter')
                                    ->label('Horímetro inicial previsto')
                                    ->numeric()
                                    ->suffix(' h'),

                                Textarea::make('notes')
                                    ->label('Observações do item')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('4. Valores e observações')
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('discount_value')
                            ->label('Desconto geral')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('additional_value')
                            ->label('Acréscimo geral')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('deposit_value')
                            ->label('Caução')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        TextInput::make('total_value')
                            ->label('Total')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(),

                        Textarea::make('commercial_notes')
                            ->label('Observações comerciais')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('operational_notes')
                            ->label('Observações operacionais')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('cancellation_reason')
                            ->label('Motivo do cancelamento')
                            ->visible(fn (callable $get): bool => $get('status') === 'cancelled')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
