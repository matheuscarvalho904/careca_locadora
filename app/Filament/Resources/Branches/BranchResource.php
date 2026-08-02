<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Models\Branch;
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

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $modelLabel = 'filial';
    protected static ?string $navigationLabel = 'Filiais';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Filiais')->columns(3)->schema([
                
Select::make('company_id')->label('Empresa')->relationship('company','legal_name')->required()->searchable()->preload(),
TextInput::make('code')->label('Código')->required()->maxLength(20),
TextInput::make('name')->label('Nome')->required()->maxLength(150),
TextInput::make('cnpj')->label('CNPJ')->maxLength(14),
TextInput::make('email')->label('E-mail')->email()->maxLength(150),
TextInput::make('phone')->label('Telefone')->maxLength(20),
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
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge(),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }
}
