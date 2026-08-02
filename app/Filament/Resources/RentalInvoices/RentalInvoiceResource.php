<?php

namespace App\Filament\Resources\RentalInvoices;

use App\Filament\Resources\RentalInvoices\Pages\EditRentalInvoice;
use App\Filament\Resources\RentalInvoices\Pages\ListRentalInvoices;
use App\Filament\Resources\RentalInvoices\Schemas\RentalInvoiceForm;
use App\Filament\Resources\RentalInvoices\Tables\RentalInvoicesTable;
use App\Models\RentalInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RentalInvoiceResource extends Resource
{
    protected static ?string $model = RentalInvoice::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'fatura de locação';
    protected static ?string $pluralModelLabel = 'faturas de locação';
    protected static ?string $navigationLabel = 'Faturas de locação';
    protected static string | UnitEnum | null $navigationGroup = 'Financeiro';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RentalInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalInvoicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalInvoices::route('/'),
            'edit' => EditRentalInvoice::route('/{record}/edit'),
        ];
    }
}
