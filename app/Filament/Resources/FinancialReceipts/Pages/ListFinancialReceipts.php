<?php

namespace App\Filament\Resources\FinancialReceipts\Pages;

use App\Filament\Resources\FinancialReceipts\FinancialReceiptResource;
use Filament\Resources\Pages\ListRecords;

class ListFinancialReceipts extends ListRecords
{
    protected static string $resource = FinancialReceiptResource::class;

    protected static ?string $title = 'Recebimentos';
}
