<?php

namespace App\Filament\Resources\FinancialReceipts;

use App\Filament\Resources\FinancialReceipts\Pages\EditFinancialReceipt;
use App\Filament\Resources\FinancialReceipts\Pages\ListFinancialReceipts;
use App\Filament\Resources\FinancialReceipts\Schemas\FinancialReceiptForm;
use App\Filament\Resources\FinancialReceipts\Tables\FinancialReceiptsTable;
use App\Models\FinancialReceipt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FinancialReceiptResource extends Resource
{
    protected static ?string $model = FinancialReceipt::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'recebimento';
    protected static ?string $pluralModelLabel = 'recebimentos';
    protected static ?string $navigationLabel = 'Recebimentos';
    protected static string | UnitEnum | null $navigationGroup = 'Financeiro';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return FinancialReceiptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialReceiptsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialReceipts::route('/'),
            'edit' => EditFinancialReceipt::route('/{record}/edit'),
        ];
    }
}
