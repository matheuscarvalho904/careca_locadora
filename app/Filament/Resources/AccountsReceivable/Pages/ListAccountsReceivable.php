<?php

namespace App\Filament\Resources\AccountsReceivable\Pages;

use App\Filament\Resources\AccountsReceivable\AccountReceivableResource;
use Filament\Resources\Pages\ListRecords;

class ListAccountsReceivable extends ListRecords
{
    protected static string $resource = AccountReceivableResource::class;
}
