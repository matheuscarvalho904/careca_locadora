<?php

namespace App\Services\Fleet;

use App\Models\AssetCategory;
use App\Services\Numbering\NumberSequenceService;
use RuntimeException;

class AssetPrefixService
{
    public function __construct(
        private readonly NumberSequenceService $numberSequenceService,
    ) {
    }

    public function next(string $organizationId, string $categoryId): string
    {
        $category = AssetCategory::query()
            ->withoutOrganizationScope()
            ->where('organization_id', $organizationId)
            ->find($categoryId);

        if ($category === null) {
            throw new RuntimeException('Categoria do ativo não encontrada.');
        }

        return $this->numberSequenceService->next(
            organizationId: $organizationId,
            key: 'asset_category_'.$category->id,
            name: 'Prefixo de ativos — '.$category->name,
            prefix: strtoupper($category->prefix).'-',
            padding: 2,
        );
    }
}
