<?php

namespace App\Filament\Resources\RentalReturns;

use App\Filament\Resources\RentalReturns\Pages\EditRentalReturn;
use App\Filament\Resources\RentalReturns\Pages\ListRentalReturns;
use App\Filament\Resources\RentalReturns\Schemas\RentalReturnForm;
use App\Filament\Resources\RentalReturns\Tables\RentalReturnsTable;
use App\Models\RentalReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RentalReturnResource extends Resource
{
    protected static ?string $model = RentalReturn::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'devolução';
    protected static ?string $pluralModelLabel = 'devoluções';
    protected static ?string $navigationLabel = 'Devoluções';
    protected static string | UnitEnum | null $navigationGroup = 'Locações';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return RentalReturnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalReturnsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalReturns::route('/'),
            'edit' => EditRentalReturn::route('/{record}/edit'),
        ];
    }
}
