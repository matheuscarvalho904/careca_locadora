<?php

namespace App\Filament\Resources\ProductBrands\Pages;

use App\Filament\Resources\ProductBrands\ProductBrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductBrand extends CreateRecord
{
    protected static string $resource = ProductBrandResource::class;
}
