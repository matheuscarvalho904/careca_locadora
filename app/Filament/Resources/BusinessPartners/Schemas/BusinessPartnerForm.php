<?php

namespace App\Filament\Resources\BusinessPartners\Schemas;

use App\Support\UI\BrazilInputMask;
use App\Services\ExternalData\CepLookupService;
use App\Services\ExternalData\CnpjLookupService;
use App\Support\UI\PremiumFormLayout;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;

class BusinessPartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1. Identificação e classificação')
                    ->description('Dados principais, classificação e papéis comerciais do parceiro.')
                    ->columnSpanFull()
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Gerado automaticamente'),

                        Select::make('person_type')
                            ->label('Tipo de pessoa')
                            ->required()
                            ->default('legal')
                            ->live()
                            ->options([
                                'legal' => 'Pessoa jurídica',
                                'individual' => 'Pessoa física',
                            ]),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('active')
                            ->options([
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'blocked' => 'Bloqueado',
                            ]),

                        CheckboxList::make('roles')
                            ->label('Papéis do parceiro')
                            ->required()
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'xl' => 4,
                            ])
                            ->options([
                                'customer' => 'Cliente',
                                'supplier' => 'Fornecedor',
                                'carrier' => 'Transportador',
                                'service_provider' => 'Prestador de serviços',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('legal_name')
                            ->label(fn (callable $get): string =>
                                $get('person_type') === 'individual'
                                    ? 'Nome completo'
                                    : 'Razão social'
                            )
                            ->required()
                            ->maxLength(200)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('trade_name')
                            ->label(fn (callable $get): string =>
                                $get('person_type') === 'individual'
                                    ? 'Nome social ou apelido'
                                    : 'Nome fantasia'
                            )
                            ->maxLength(200)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),
                    ]),

                Section::make('2. Documentação fiscal')
                    ->description('CPF/CNPJ, inscrições e informações cadastrais oficiais.')
                    ->columnSpanFull()
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('document')
                            ->label('CPF/CNPJ')
                            ->mask(BrazilInputMask::cpfCnpj())
                            ->stripCharacters(BrazilInputMask::documentStripCharacters())
                            ->maxLength(20)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) =>
                                    $rule->where(
                                        'organization_id',
                                        auth()->user()?->organization_id,
                                    ),
                            )
                            ->dehydrateStateUsing(
                                fn (?string $state): ?string =>
                                    preg_replace('/\D+/', '', (string) $state) ?: null
                            )
                            ->suffixAction(
                                Action::make('lookupCnpj')
                                    ->icon(Heroicon::MagnifyingGlass)
                                    ->tooltip('Consultar CNPJ')
                                    ->visible(
                                        fn (callable $get): bool =>
                                            $get('person_type') === 'legal'
                                    )
                                    ->action(function (?string $state, callable $set): void {
                                        try {
                                            $data = app(CnpjLookupService::class)
                                                ->lookup((string) $state);

                                            $map = [
                                                'legal_name' => 'legal_name',
                                                'trade_name' => 'trade_name',
                                                'document' => 'document',
                                                'state_registration' => 'state_registration',
                                                'registration_status' => 'registration_status',
                                                'cnae' => 'main_cnae_code',
                                                'opened_at' => 'opened_at',
                                                'email' => 'email',
                                                'phone' => 'phone',
                                                'company_size' => 'company_size',
                                                'external_data' => 'external_data',
                                                'external_data_synced_at' => 'external_data_synced_at',
                                            ];

                                            foreach ($map as $source => $target) {
                                                if (($data[$source] ?? null) !== null) {
                                                    $set($target, $data[$source]);
                                                }
                                            }

                                            $set('addresses', [[
                                                'type' => 'main',
                                                'label' => 'Endereço principal',
                                                'postal_code' => $data['postal_code'] ?? null,
                                                'address' => $data['address'] ?? null,
                                                'number' => $data['address_number'] ?? null,
                                                'complement' => $data['address_complement'] ?? null,
                                                'district' => $data['district'] ?? null,
                                                'city' => $data['city'] ?? null,
                                                'state' => $data['state'] ?? null,
                                                'is_primary' => true,
                                            ]]);

                                            Notification::make()
                                                ->success()
                                                ->title('CNPJ consultado')
                                                ->body('Revise os dados antes de salvar.')
                                                ->send();
                                        } catch (Throwable $exception) {
                                            Notification::make()
                                                ->danger()
                                                ->title('Falha na consulta')
                                                ->body($exception->getMessage())
                                                ->send();
                                        }
                                    })
                            )
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('state_registration')
                            ->label('Inscrição estadual')
                            ->maxLength(30),

                        TextInput::make('municipal_registration')
                            ->label('Inscrição municipal')
                            ->maxLength(30),

                        TextInput::make('registration_status')
                            ->label('Situação cadastral')
                            ->maxLength(50)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('main_cnae_code')
                            ->label('CNAE principal')
                            ->maxLength(20),

                        DatePicker::make('opened_at')
                            ->label('Data de abertura')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ]),

                Section::make('3. Contato e crédito')
                    ->description('Canais principais, limite e condição comercial.')
                    ->columnSpanFull()
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        TextInput::make('email')
                            ->label('E-mail principal')
                            ->email()
                            ->maxLength(150)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->mask(BrazilInputMask::phone())
                            ->stripCharacters(BrazilInputMask::phoneStripCharacters())
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->mask(BrazilInputMask::phone())
                            ->stripCharacters(BrazilInputMask::phoneStripCharacters())
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('credit_limit')
                            ->label('Limite de crédito')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ]),

                        TextInput::make('payment_term_days')
                            ->label('Prazo padrão')
                            ->numeric()
                            ->suffix('dias')
                            ->default(0),

                        TextInput::make('payment_condition')
                            ->label('Condição de pagamento')
                            ->maxLength(100),
                    ]),

                Section::make('4. Endereços')
                    ->description('Endereço principal, cobrança, entrega e correspondência.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('addresses')
                            ->label('')
                            ->relationship()
                            ->defaultItems(1)
                            ->collapsible()
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['label']
                                    ?? $state['city']
                                    ?? 'Endereço'
                            )
                            ->columns(PremiumFormLayout::repeater())
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo')
                                    ->default('main')
                                    ->options([
                                        'main' => 'Principal',
                                        'billing' => 'Cobrança',
                                        'delivery' => 'Entrega',
                                        'correspondence' => 'Correspondência',
                                    ]),

                                TextInput::make('label')
                                    ->label('Identificação')
                                    ->maxLength(100),

                                TextInput::make('postal_code')
                                    ->label('CEP')
                                    ->mask(BrazilInputMask::postalCode())
                                    ->stripCharacters(BrazilInputMask::postalCodeStripCharacters())
                                    ->maxLength(10)
                                    ->dehydrateStateUsing(
                                        fn (?string $state): ?string =>
                                            preg_replace('/\D+/', '', (string) $state) ?: null
                                    )
                                    ->suffixAction(
                                        Action::make('lookupCep')
                                            ->icon(Heroicon::MagnifyingGlass)
                                            ->tooltip('Consultar CEP')
                                            ->action(function (?string $state, callable $set): void {
                                                try {
                                                    $data = app(CepLookupService::class)
                                                        ->lookup((string) $state);

                                                    $mapping = [
                                                        'postal_code' => 'postal_code',
                                                        'address' => 'address',
                                                        'address_complement' => 'complement',
                                                        'district' => 'district',
                                                        'city' => 'city',
                                                        'state' => 'state',
                                                    ];

                                                    foreach ($mapping as $source => $target) {
                                                        if (($data[$source] ?? null) !== null) {
                                                            $set($target, $data[$source]);
                                                        }
                                                    }
                                                } catch (Throwable $exception) {
                                                    Notification::make()
                                                        ->danger()
                                                        ->title('Falha na consulta do CEP')
                                                        ->body($exception->getMessage())
                                                        ->send();
                                                }
                                            })
                                    ),

                                Toggle::make('is_primary')
                                    ->label('Endereço principal'),

                                TextInput::make('address')
                                    ->label('Logradouro')
                                    ->maxLength(200)
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                TextInput::make('number')
                                    ->label('Número')
                                    ->maxLength(20),

                                TextInput::make('complement')
                                    ->label('Complemento')
                                    ->maxLength(100),

                                TextInput::make('district')
                                    ->label('Bairro')
                                    ->maxLength(100),

                                TextInput::make('city')
                                    ->label('Cidade')
                                    ->maxLength(100)
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                Select::make('state')
                                    ->label('UF')
                                    ->searchable()
                                    ->options(self::states()),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('5. Contatos')
                    ->description('Responsáveis comerciais, financeiros e operacionais.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('contacts')
                            ->label('')
                            ->relationship()
                            ->collapsible()
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['name'] ?? 'Contato'
                            )
                            ->columns(PremiumFormLayout::repeater())
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(150)
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                TextInput::make('position')
                                    ->label('Cargo ou função')
                                    ->maxLength(100),

                                TextInput::make('department')
                                    ->label('Departamento')
                                    ->maxLength(100),

                                TextInput::make('email')
                                    ->label('E-mail')
                                    ->email()
                                    ->maxLength(150)
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                TextInput::make('phone')
                                    ->label('Telefone')
                            ->mask(BrazilInputMask::phone())
                            ->stripCharacters(BrazilInputMask::phoneStripCharacters())
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                            ->mask(BrazilInputMask::phone())
                            ->stripCharacters(BrazilInputMask::phoneStripCharacters())
                                    ->tel()
                                    ->maxLength(20),

                                Toggle::make('is_primary')
                                    ->label('Contato principal'),

                                Textarea::make('notes')
                                    ->label('Observações')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('6. Informações adicionais')
                    ->columnSpanFull()
                    ->columns(PremiumFormLayout::standard())
                    ->schema([
                        Select::make('company_size')
                            ->label('Porte')
                            ->options([
                                'mei' => 'MEI',
                                'micro' => 'Microempresa',
                                'small' => 'Pequeno porte',
                                'medium' => 'Médio porte',
                                'large' => 'Grande porte',
                            ]),

                        TagsInput::make('tags')
                            ->label('Tags')
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                            ]),

                        Textarea::make('notes')
                            ->label('Observações gerais')
                            ->rows(4)
                            ->columnSpanFull(),

                        Hidden::make('external_data'),
                        Hidden::make('external_data_synced_at'),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function states(): array
    {
        return [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins',
        ];
    }
}
