<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
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
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel = 'usuário';
    protected static ?string $navigationLabel = 'Usuários';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Usuários')->columns(3)->schema([
                
TextInput::make('name')->label('Nome')->required()->maxLength(150),
TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord:true),
TextInput::make('cpf')->label('CPF')->maxLength(11),
TextInput::make('phone')->label('Telefone')->maxLength(20),
TextInput::make('whatsapp')->label('WhatsApp')->maxLength(20),
TextInput::make('job_title')->label('Cargo')->maxLength(150),
Select::make('default_company_id')->label('Empresa padrão')->relationship('defaultCompany','legal_name')->searchable()->preload(),
Select::make('default_branch_id')->label('Filial padrão')->relationship('defaultBranch','name')->searchable()->preload(),
Select::make('roles')->label('Perfis de acesso')->multiple()->relationship('roles','name')->preload()->searchable(),
Toggle::make('must_change_password')->label('Exigir troca de senha'),
Toggle::make('is_platform_admin')->label('Administrador da plataforma'),
Select::make('status')->label('Status')->options([
    'invited'=>'Convidado','active'=>'Ativo','blocked'=>'Bloqueado','inactive'=>'Inativo'
])->default('invited')->required(),
TextInput::make('password')->label('Senha')->password()->revealable()
    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
    ->dehydrated(fn (?string $state): bool => filled($state))
    ->required(fn (string $operation): bool => $operation === 'create'),

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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
