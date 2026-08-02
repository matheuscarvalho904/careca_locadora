<?php

namespace App\Support\UI;

use Filament\Tables\Table;

final class PremiumTable
{
    public static function apply(Table $table): Table
    {
        return $table
            ->striped()
            ->deferLoading()
            ->persistColumnSearchesInSession()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    private function __construct()
    {
    }
}
