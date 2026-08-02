<?php
namespace App\Filament\Resources\ServiceOrders\Pages;
use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Resources\Pages\EditRecord;
class EditServiceOrder extends EditRecord
{
    protected static string $resource = ServiceOrderResource::class;
}
