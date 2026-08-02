<?php

namespace App\Filament\Resources\FinancialAccounts;

use App\Filament\Resources\FinancialAccounts\Pages\CreateFinancialAccount;
use App\Filament\Resources\FinancialAccounts\Pages\EditFinancialAccount;
use App\Filament\Resources\FinancialAccounts\Pages\ListFinancialAccounts;
use App\Filament\Resources\FinancialAccounts\Schemas\FinancialAccountForm;
use App\Filament\Resources\FinancialAccounts\Tables\FinancialAccountsTable;
use App\Models\FinancialAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FinancialAccountResource extends Resource
{
    protected static ?string $model = FinancialAccount::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'conta financeira';
    protected static ?string $pluralModelLabel = 'contas financeiras';
    protected static ?string $navigationLabel = 'Caixa e bancos';
    protected static string | UnitEnum | null $navigationGroup = 'Financeiro';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return FinancialAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialAccountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialAccounts::route('/'),
            'create' => CreateFinancialAccount::route('/create'),
            'edit' => EditFinancialAccount::route('/{record}/edit'),
        ];
    }
}
