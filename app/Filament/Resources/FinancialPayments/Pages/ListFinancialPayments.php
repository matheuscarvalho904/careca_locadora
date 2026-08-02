<?php

namespace App\Filament\Resources\FinancialPayments\Pages;

use App\Filament\Resources\FinancialPayments\FinancialPaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListFinancialPayments extends ListRecords
{
    protected static string $resource = FinancialPaymentResource::class;

    protected static ?string $title = 'Pagamentos';
}
