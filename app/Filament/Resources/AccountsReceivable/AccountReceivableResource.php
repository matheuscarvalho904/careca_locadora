<?php

namespace App\Filament\Resources\AccountsReceivable;

use App\Filament\Resources\AccountsReceivable\Pages\EditAccountReceivable;
use App\Filament\Resources\AccountsReceivable\Pages\ListAccountsReceivable;
use App\Filament\Resources\AccountsReceivable\Schemas\AccountReceivableForm;
use App\Filament\Resources\AccountsReceivable\Tables\AccountsReceivableTable;
use App\Models\AccountReceivable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AccountReceivableResource extends Resource
{
    protected static ?string $model = AccountReceivable::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'conta a receber';
    protected static ?string $pluralModelLabel = 'contas a receber';
    protected static ?string $navigationLabel = 'Contas a receber';
    protected static string | UnitEnum | null $navigationGroup = 'Financeiro';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AccountReceivableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountsReceivableTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountsReceivable::route('/'),
            'edit' => EditAccountReceivable::route('/{record}/edit'),
        ];
    }
}
