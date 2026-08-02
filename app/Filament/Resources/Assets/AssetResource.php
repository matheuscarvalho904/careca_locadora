<?php

namespace App\Filament\Resources\Assets;

use App\Filament\Resources\Assets\Pages\CreateAsset;
use App\Filament\Resources\Assets\Pages\EditAsset;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Assets\Schemas\AssetForm;
use App\Filament\Resources\Assets\Tables\AssetsTable;
use App\Models\Asset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'ativo';
    protected static ?string $pluralModelLabel = 'ativos';
    protected static ?string $navigationLabel = 'Ativos';
    protected static string | UnitEnum | null $navigationGroup = 'Frota';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetsTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['prefix', 'name', 'plate', 'renavam', 'chassis', 'brand', 'model'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssets::route('/'),
            'create' => CreateAsset::route('/create'),
            'edit' => EditAsset::route('/{record}/edit'),
        ];
    }
}
