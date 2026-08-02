<?php

namespace App\Filament\Resources\Companies;

use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Models\Company;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $modelLabel = 'empresa';
    protected static ?string $navigationLabel = 'Empresas';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Empresas')->columns(3)->schema([
                
TextInput::make('legal_name')->label('Razão social')->required()->maxLength(200),
TextInput::make('trade_name')->label('Nome fantasia')->maxLength(200),
TextInput::make('cnpj')->label('CNPJ')->required()->maxLength(14),
TextInput::make('state_registration')->label('Inscrição estadual')->maxLength(30),
TextInput::make('municipal_registration')->label('Inscrição municipal')->maxLength(30),
TextInput::make('email')->label('E-mail')->email()->maxLength(150),
TextInput::make('phone')->label('Telefone')->maxLength(20),
TextInput::make('whatsapp')->label('WhatsApp')->maxLength(20),
Select::make('tax_regime')->label('Regime tributário')->options([
    'simples_nacional'=>'Simples Nacional',
    'lucro_presumido'=>'Lucro Presumido',
    'lucro_real'=>'Lucro Real',
    'mei'=>'MEI',
]),
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
            TextColumn::make('status')->label('Status')->badge(),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
