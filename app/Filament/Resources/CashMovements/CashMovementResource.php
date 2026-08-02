<?php

namespace App\Filament\Resources\CashMovements;

use App\Filament\Resources\CashMovements\Pages\ListCashMovements;
use App\Filament\Resources\CashMovements\Tables\CashMovementsTable;
use App\Models\CashMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class CashMovementResource extends Resource
{
    protected static ?string $model = CashMovement::class;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'movimentação financeira';

    protected static ?string $pluralModelLabel = 'movimentações financeiras';

    protected static ?string $navigationLabel = 'Extrato financeiro';

    protected static string | UnitEnum | null $navigationGroup = 'Financeiro';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return CashMovementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashMovements::route('/'),
        ];
    }
}
