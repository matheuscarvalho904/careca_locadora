<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Models\Branch;
use App\Services\ExternalData\CepLookupService;
use App\Services\ExternalData\CnpjLookupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;
use UnitEnum;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $modelLabel = 'filial';
    protected static ?string $pluralModelLabel = 'filiais';
    protected static ?string $navigationLabel = 'Filiais';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Cadastro inteligente da filial')->columns(4)->schema([
                Hidden::make('organization_id')
                    ->default(fn (): ?string => auth()->user()?->organization_id)
                    ->dehydrated()
                    ->required(),

                Select::make('company_id')->label('Empresa')->relationship('company','legal_name')->required()->searchable()->preload(),
                TextInput::make('code')->label('Código')->required()->maxLength(20),
                TextInput::make('name')->label('Nome da filial')->required()->maxLength(150),

                TextInput::make('cnpj')
                    ->label('CNPJ')
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
                                        'municipal_registration'=>'municipal_registration',
                                        'email'=>'email',
                                        'phone'=>'phone',
                                        'postal_code'=>'postal_code',
                                        'address'=>'street',
                                        'address_number'=>'number',
                                        'address_complement'=>'complement',
                                        'district'=>'district',
                                        'city'=>'city',
                                        'state'=>'state',
                                    ] as $source=>$target) {
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

                TextInput::make('legal_name')->label('Razão social')->maxLength(200)->columnSpan(2),
                TextInput::make('trade_name')->label('Nome fantasia')->maxLength(200)->columnSpan(2),
                TextInput::make('state_registration')->label('Inscrição estadual')->maxLength(30),
                TextInput::make('municipal_registration')->label('Inscrição municipal')->maxLength(30),
                TextInput::make('email')->label('E-mail')->email()->maxLength(150),
                TextInput::make('phone')->label('Telefone')->maxLength(20),
                TextInput::make('whatsapp')->label('WhatsApp')->maxLength(20),

                TextInput::make('postal_code')
                    ->label('CEP')
                    ->maxLength(10)
                    ->suffixAction(
                        Action::make('consultarCep')
                            ->icon(Heroicon::MagnifyingGlass)
                            ->action(function (?string $state, callable $set): void {
                                try {
                                    $data = app(CepLookupService::class)->lookup((string) $state);
                                    foreach ([
                                        'postal_code'=>'postal_code',
                                        'address'=>'street',
                                        'address_complement'=>'complement',
                                        'district'=>'district',
                                        'city'=>'city',
                                        'state'=>'state',
                                    ] as $source=>$target) {
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
                TextInput::make('number')->label('Número')->maxLength(30),
                TextInput::make('complement')->label('Complemento')->maxLength(100),
                TextInput::make('district')->label('Bairro')->maxLength(150),
                TextInput::make('city')->label('Cidade')->maxLength(150),
                TextInput::make('state')->label('UF')->maxLength(2),
                Toggle::make('is_headquarters')->label('Matriz'),
                Toggle::make('allows_rentals')->label('Permite locações')->default(true),
                Toggle::make('allows_maintenance')->label('Permite manutenção')->default(true),
                Toggle::make('allows_inventory')->label('Permite estoque')->default(true),
                Select::make('status')->label('Status')->options(['active'=>'Ativa','inactive'=>'Inativa'])->default('active')->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable(),
            TextColumn::make('name')->label('Filial')->searchable()->sortable(),
            TextColumn::make('company.legal_name')->label('Empresa'),
            TextColumn::make('city')->label('Cidade')->placeholder('—'),
            IconColumn::make('is_headquarters')->label('Matriz')->boolean(),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn(string $state): string => $state === 'active' ? 'Ativa' : 'Inativa'),
        ])->recordActions([EditAction::make()->label('Abrir')]);
    }

    public static function getPages(): array
    {
        return [
            'index'=>ListBranches::route('/'),
            'create'=>CreateBranch::route('/create'),
            'edit'=>EditBranch::route('/{record}/edit'),
        ];
    }
}
