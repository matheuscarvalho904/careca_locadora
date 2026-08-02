<?php

namespace App\Filament\Resources\BusinessPartners;

use App\Filament\Resources\BusinessPartners\Pages\CreateBusinessPartner;
use App\Filament\Resources\BusinessPartners\Pages\EditBusinessPartner;
use App\Filament\Resources\BusinessPartners\Pages\ListBusinessPartners;
use App\Filament\Resources\BusinessPartners\Schemas\BusinessPartnerForm;
use App\Filament\Resources\BusinessPartners\Tables\BusinessPartnersTable;
use App\Models\BusinessPartner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BusinessPartnerResource extends Resource
{
    protected static ?string $model = BusinessPartner::class;
    protected static ?string $recordTitleAttribute = 'trade_name';
    protected static ?string $modelLabel = 'parceiro';
    protected static ?string $pluralModelLabel = 'parceiros';
    protected static ?string $navigationLabel = 'Clientes e Parceiros';
    protected static string | UnitEnum | null $navigationGroup = 'CRM';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BusinessPartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessPartnersTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'legal_name', 'trade_name', 'document', 'email'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessPartners::route('/'),
            'create' => CreateBusinessPartner::route('/create'),
            'edit' => EditBusinessPartner::route('/{record}/edit'),
        ];
    }
}
