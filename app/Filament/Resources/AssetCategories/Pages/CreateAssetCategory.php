<?php

namespace App\Filament\Resources\AssetCategories\Pages;

use App\Filament\Resources\AssetCategories\AssetCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetCategory extends CreateRecord
{
    protected static string $resource = AssetCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = auth()->user()?->organization_id;

        return $data;
    }
}
