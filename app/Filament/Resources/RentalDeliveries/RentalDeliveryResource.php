<?php

namespace App\Filament\Resources\RentalDeliveries;

use App\Filament\Resources\RentalDeliveries\Pages\EditRentalDelivery;
use App\Filament\Resources\RentalDeliveries\Pages\ListRentalDeliveries;
use App\Filament\Resources\RentalDeliveries\Pages\ManageDeliveryDamageMap;
use App\Filament\Resources\RentalDeliveries\Pages\ManageDeliveryChecklistPremium;
use App\Filament\Resources\RentalDeliveries\Schemas\RentalDeliveryForm;
use App\Filament\Resources\RentalDeliveries\Tables\RentalDeliveriesTable;
use App\Models\RentalDelivery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RentalDeliveryResource extends Resource
{
    protected static ?string $model = RentalDelivery::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'entrega';
    protected static ?string $pluralModelLabel = 'entregas';
    protected static ?string $navigationLabel = 'Entregas';
    protected static string | UnitEnum | null $navigationGroup = 'Locações';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return RentalDeliveryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalDeliveriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalDeliveries::route('/'),
            'edit' => EditRentalDelivery::route('/{record}/edit'),
            'damage-map' => ManageDeliveryDamageMap::route('/{record}/damage-map'),
            'checklist-premium' => ManageDeliveryChecklistPremium::route('/{record}/checklist-premium'),
        ];
    }
}
