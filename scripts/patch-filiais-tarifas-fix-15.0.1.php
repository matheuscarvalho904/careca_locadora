<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);
$model = $root . '/app/Models/RentalRatePlan.php';
$createPage = $root . '/app/Filament/Resources/RentalRatePlans/Pages/CreateRentalRatePlan.php';

foreach ([$model, $createPage] as $path) {
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }
}

$modelContent = file_get_contents($model);

$methods = '';

if (! str_contains($modelContent, 'function assetCategory()')) {
    $methods .= <<<'PHP'

    public function assetCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(
            \App\Models\AssetCategory::class,
            'asset_category_id'
        );
    }
PHP;
}

if (! str_contains($modelContent, 'function branch()')) {
    $methods .= <<<'PHP'

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
PHP;
}

if ($methods !== '') {
    $position = strrpos($modelContent, '}');

    if ($position === false) {
        throw new RuntimeException(
            'Fechamento da classe RentalRatePlan nao localizado.'
        );
    }

    $modelContent =
        substr($modelContent, 0, $position)
        . $methods
        . PHP_EOL
        . substr($modelContent, $position);

    file_put_contents($model, $modelContent);
    echo "[CORRIGIDO] Relacoes do RentalRatePlan adicionadas." . PHP_EOL;
} else {
    echo "[SEM ALTERACAO] Relacoes do RentalRatePlan ja existem." . PHP_EOL;
}

$pageContent = file_get_contents($createPage);

if (! str_contains($pageContent, 'mutateFormDataBeforeCreate')) {
    $position = strrpos($pageContent, '}');

    if ($position === false) {
        throw new RuntimeException(
            'Fechamento da classe CreateRentalRatePlan nao localizado.'
        );
    }

    $method = <<<'PHP'

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $organizationId = auth()->user()?->organization_id
                ?? app(\App\Support\Tenancy\TenantContext::class)
                    ->organizationId();

            if (blank($organizationId)) {
                throw new \RuntimeException(
                    'Organização não identificada para gerar a tarifa.'
                );
            }

            $data['organization_id'] = $organizationId;
            $data['code'] = app(
                \App\Services\Numbering\NumberSequenceService::class
            )->next(
                organizationId: $organizationId,
                key: 'rental_rate_plan',
                name: 'Tarifas de locação',
                prefix: 'TAR-',
                padding: 4,
            );
        }

        return $data;
    }
PHP;

    $pageContent =
        substr($pageContent, 0, $position)
        . $method
        . PHP_EOL
        . substr($pageContent, $position);

    file_put_contents($createPage, $pageContent);
    echo "[CORRIGIDO] Codigo automatico de tarifa adicionado." . PHP_EOL;
} else {
    echo "[SEM ALTERACAO] Codigo automatico de tarifa ja existe." . PHP_EOL;
}
