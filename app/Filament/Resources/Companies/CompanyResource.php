<?php

namespace App\Filament\Resources\Companies;

use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Models\Company;
use App\Services\ExternalData\CepLookupService;
use App\Services\ExternalData\CnpjLookupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;
use UnitEnum;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $modelLabel = 'empresa';
    protected static ?string $pluralModelLabel = 'empresas';
    protected static ?string $navigationLabel = 'Empresas';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Cadastro inteligente da empresa')->columns(4)->schema([
                Hidden::make('organization_id')
                    ->default(fn (): ?string => auth()->user()?->organization_id)
                    ->dehydrated()
                    ->required(),

                TextInput::make('cnpj')
                    ->label('CNPJ')
                    ->required()
                    ->maxLength(18)
                    ->dehydrateStateUsing(fn (?string $state): ?string =>
                        preg_replace('/\D+/', '', (string) $state) ?: null
                    )
                    ->suffixAction(
                        Action::make('consultarCnpj')
                            ->icon(Heroicon::MagnifyingGlass)
                            ->tooltip('Consultar CNPJ')
                            ->action(function (?string $state, callable $set): void {
                                try {
                                    $data = app(CnpjLookupService::class)->lookup((string) $state);
                                    foreach ([
                                        'legal_name'=>'legal_name',
                                        'trade_name'=>'trade_name',
                                        'document'=>'cnpj',
                                        'state_registration'=>'state_registration',
                                        'registration_status'=>'registration_status',
                                        'cnae'=>'main_cnae_code',
                                        'opened_at'=>'opened_at',
                                        'email'=>'email',
                                        'phone'=>'phone',
                                        'company_size'=>'company_size',
                                        'postal_code'=>'postal_code',
                                        'address'=>'street',
                                        'address_number'=>'address_number',
                                        'address_complement'=>'address_complement',
                                        'district'=>'district',
                                        'city'=>'city',
                                        'state'=>'state',
                                    ] as $source => $target) {
                                        if (($data[$source] ?? null) !== null) {
                                            $set($target, $data[$source]);
                                        }
                                    }
                                    Notification::make()->success()->title('CNPJ consultado')->send();
                                } catch (Throwable $exception) {
                                    Notification::make()->danger()->title('Falha na consulta')->body($exception->getMessage())->send();
                                }
                            })
                    ),

                TextInput::make('legal_name')->label('Razão social')->required()->maxLength(200)->columnSpan(2),
                TextInput::make('trade_name')->label('Nome fantasia')->maxLength(200),
                TextInput::make('state_registration')->label('Inscrição estadual')->maxLength(30),
                TextInput::make('municipal_registration')->label('Inscrição municipal')->maxLength(30),
                TextInput::make('registration_status')->label('Situação cadastral')->maxLength(50),
                TextInput::make('main_cnae_code')->label('CNAE principal')->maxLength(20),
                DatePicker::make('opened_at')->label('Data de abertura')->native(false)->displayFormat('d/m/Y'),
                TextInput::make('email')->label('E-mail')->email()->maxLength(150),
                TextInput::make('phone')->label('Telefone')->maxLength(20),
                TextInput::make('whatsapp')->label('WhatsApp')->maxLength(20),

                TextInput::make('postal_code')
                    ->label('CEP')
                    ->maxLength(10)
                    ->suffixAction(
                        Action::make('consultarCep')
                            ->icon(Heroicon::MagnifyingGlass)
                            ->tooltip('Consultar CEP')
                            ->action(function (?string $state, callable $set): void {
                                try {
                                    $data = app(CepLookupService::class)->lookup((string) $state);
                                    foreach ([
                                        'postal_code'=>'postal_code',
                                        'address'=>'street',
                                        'address_complement'=>'address_complement',
                                        'district'=>'district',
                                        'city'=>'city',
                                        'state'=>'state',
                                    ] as $source => $target) {
                                        if (($data[$source] ?? null) !== null) {
                                            $set($target, $data[$source]);
                                        }
                                    }
                                } catch (Throwable $exception) {
                                    Notification::make()->danger()->title('Falha na consulta do CEP')->body($exception->getMessage())->send();
                                }
                            })
                    ),

                TextInput::make('street')->label('Logradouro')->maxLength(200)->columnSpan(2),
                TextInput::make('address_number')->label('Número')->maxLength(30),
                TextInput::make('address_complement')->label('Complemento')->maxLength(100),
                TextInput::make('district')->label('Bairro')->maxLength(150),
                TextInput::make('city')->label('Cidade')->maxLength(150),
                TextInput::make('state')->label('UF')->maxLength(2),

                Select::make('tax_regime')->label('Regime tributário')->options([
                    'simples_nacional'=>'Simples Nacional',
                    'lucro_presumido'=>'Lucro Presumido',
                    'lucro_real'=>'Lucro Real',
                    'mei'=>'MEI',
                ]),
                Toggle::make('simple_national')->label('Simples Nacional'),
                Toggle::make('mei')->label('MEI'),
                FileUpload::make('logo_path')->label('Logomarca')->image()->directory('companies/logos')->disk('public'),
                Select::make('status')->label('Status')->options([
                    'active'=>'Ativa',
                    'inactive'=>'Inativa',
                    'suspended'=>'Suspensa',
                ])->default('active')->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('legal_name')->label('Razão social')->searchable()->sortable(),
            TextColumn::make('trade_name')->label('Nome fantasia')->searchable()->placeholder('—'),
            TextColumn::make('cnpj')->label('CNPJ')->searchable(),
            TextColumn::make('city')->label('Cidade')->placeholder('—'),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'active'=>'Ativa','inactive'=>'Inativa','suspended'=>'Suspensa',default=>$state
                }),
        ])->recordActions([EditAction::make()->label('Abrir')]);
    }

    public static function getPages(): array
    {
        return [
            'index'=>ListCompanies::route('/'),
            'create'=>CreateCompany::route('/create'),
            'edit'=>EditCompany::route('/{record}/edit'),
        ];
    }
}
