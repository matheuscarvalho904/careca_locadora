<?php

namespace App\Filament\Resources\RentalContracts;

use App\Filament\Resources\RentalContracts\Pages\EditRentalContract;
use App\Filament\Resources\RentalContracts\Pages\ListRentalContracts;
use App\Filament\Resources\RentalContracts\Schemas\RentalContractForm;
use App\Filament\Resources\RentalContracts\Tables\RentalContractsTable;
use App\Models\RentalContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RentalContractResource extends Resource
{
    protected static ?string $model = RentalContract::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'contrato';
    protected static ?string $pluralModelLabel = 'contratos';
    protected static ?string $navigationLabel = 'Contratos';
    protected static string | UnitEnum | null $navigationGroup = 'Locações';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RentalContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalContractsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalContracts::route('/'),
            'edit' => EditRentalContract::route('/{record}/edit'),
        ];
    }
}
