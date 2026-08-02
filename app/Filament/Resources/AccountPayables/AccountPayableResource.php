<?php
namespace App\Filament\Resources\AccountPayables;

use App\Filament\Resources\AccountPayables\Pages\CreateAccountPayable;
use App\Filament\Resources\AccountPayables\Pages\EditAccountPayable;
use App\Filament\Resources\AccountPayables\Pages\ListAccountPayables;
use App\Filament\Resources\AccountPayables\Schemas\AccountPayableForm;
use App\Filament\Resources\AccountPayables\Tables\AccountPayablesTable;
use App\Models\AccountPayable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AccountPayableResource extends Resource
{
    protected static ?string $model=AccountPayable::class;
    protected static ?string $recordTitleAttribute='number';
    protected static ?string $modelLabel='conta a pagar';
    protected static ?string $pluralModelLabel='contas a pagar';
    protected static ?string $navigationLabel='Contas a pagar';
    protected static string|UnitEnum|null $navigationGroup='Financeiro';
    protected static string|BackedEnum|null $navigationIcon='heroicon-o-arrow-up-circle';
    protected static ?int $navigationSort=6;
    public static function form(Schema $schema): Schema { return AccountPayableForm::configure($schema); }
    public static function table(Table $table): Table { return AccountPayablesTable::configure($table); }
    public static function getPages(): array { return ['index'=>ListAccountPayables::route('/'),'create'=>CreateAccountPayable::route('/create'),'edit'=>EditAccountPayable::route('/{record}/edit')]; }
}
