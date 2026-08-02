<?php

namespace App\Filament\Resources\RentalReservations;

use App\Filament\Resources\RentalReservations\Pages\CreateRentalReservation;
use App\Filament\Resources\RentalReservations\Pages\EditRentalReservation;
use App\Filament\Resources\RentalReservations\Pages\ListRentalReservations;
use App\Filament\Resources\RentalReservations\Schemas\RentalReservationForm;
use App\Filament\Resources\RentalReservations\Tables\RentalReservationsTable;
use App\Models\RentalReservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class RentalReservationResource extends Resource
{
    protected static ?string $model = RentalReservation::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'reserva';
    protected static ?string $pluralModelLabel = 'reservas';
    protected static ?string $navigationLabel = 'Reservas';
    protected static string | UnitEnum | null $navigationGroup = 'Locações';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RentalReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalReservationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalReservations::route('/'),
            'create' => CreateRentalReservation::route('/create'),
            'edit' => EditRentalReservation::route('/{record}/edit'),
        ];
    }
}
