<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function content(string $path): string
{
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }

    return file_get_contents($path);
}

function writeChanged(string $path, string $new, string $label): void
{
    file_put_contents($path, $new);
    echo "[CORRIGIDO] {$label}" . PHP_EOL;
}

$branchResource = $root . '/app/Filament/Resources/Branches/BranchResource.php';
$assetForm = $root . '/app/Filament/Resources/Assets/Schemas/AssetForm.php';
$assetTable = $root . '/app/Filament/Resources/Assets/Tables/AssetsTable.php';
$branchModel = $root . '/app/Models/Branch.php';
$rateModel = $root . '/app/Models/RentalRatePlan.php';
$rateCreate = $root . '/app/Filament/Resources/RentalRatePlans/Pages/CreateRentalRatePlan.php';

$branch = content($branchResource);
$branch = str_replace(
    "TextInput::make('code')->label('Código')->required()->maxLength(20),",
    "TextInput::make('code')->label('Código')->disabled()->dehydrated()->helperText('Gerado automaticamente.'),",
    $branch
);
$branch = str_replace(
    "TextInput::make('name')->label('Nome')->required()->maxLength(150),",
    "TextInput::make('name')->label('Nome da filial / cidade')->required()->maxLength(150)->helperText('Ex.: Aripuanã, Alta Floresta.'),",
    $branch
);
$branch = str_replace(
    "TextColumn::make('name')->label('Nome')",
    "TextColumn::make('name')->label('Filial')",
    $branch
);
writeChanged($branchResource, $branch, 'Filiais');

$branchModelContent = content($branchModel);
if (! str_contains($branchModelContent, "key: 'branch'")) {
    $pos = strrpos($branchModelContent, '}');
    if ($pos === false) {
        throw new RuntimeException('Classe Branch invalida.');
    }

    $method = <<<'PHP'

    protected static function booted(): void
    {
        static::creating(function (self $branch): void {
            if (blank($branch->code) && filled($branch->organization_id)) {
                $branch->code = app(
                    \App\Services\Numbering\NumberSequenceService::class
                )->next(
                    organizationId: $branch->organization_id,
                    key: 'branch',
                    name: 'Filiais',
                    prefix: 'FL-',
                    padding: 2,
                );
            }
        });
    }
PHP;

    $branchModelContent =
        substr($branchModelContent, 0, $pos)
        . $method . PHP_EOL
        . substr($branchModelContent, $pos);

    writeChanged($branchModel, $branchModelContent, 'Código automático da filial');
}

$form = content($assetForm);
$form = str_replace("titleAttribute: 'trade_name'", "titleAttribute: 'name'", $form);
writeChanged($assetForm, $form, 'Filial no cadastro de ativos');

$table = content($assetTable);
$table = str_replace("TextColumn::make('branch.trade_name')", "TextColumn::make('branch.name')", $table);
$table = str_replace("->relationship('branch', 'trade_name')", "->relationship('branch', 'name')", $table);
$table = str_replace(
    "->wrap(),\n\n                TextColumn::make('category.name')",
    "->limit(46)\n                    ->tooltip(fn (\$record): ?string => \$record->name)\n                    ->wrap(),\n\n                TextColumn::make('category.name')",
    $table
);
writeChanged($assetTable, $table, 'Tabela de ativos');

$rate = content($rateModel);
$methods = '';

if (! str_contains($rate, 'function assetCategory()')) {
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

if (! str_contains($rate, 'function branch()')) {
    $methods .= <<<'PHP'

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
PHP;
}

if ($methods !== '') {
    $pos = strrpos($rate, '}');
    if ($pos === false) {
        throw new RuntimeException('Classe RentalRatePlan invalida.');
    }

    $rate = substr($rate, 0, $pos) . $methods . PHP_EOL . substr($rate, $pos);
    writeChanged($rateModel, $rate, 'Relações das tarifas');
}

$page = content($rateCreate);
if (! str_contains($page, 'mutateFormDataBeforeCreate')) {
    $pos = strrpos($page, '}');
    if ($pos === false) {
        throw new RuntimeException('CreateRentalRatePlan invalida.');
    }

    $method = <<<'PHP'

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $organizationId = auth()->user()?->organization_id
            ?? app(\App\Support\Tenancy\TenantContext::class)
                ->organizationId();

        if (blank($organizationId)) {
            throw new \RuntimeException(
                'Organização não identificada para gerar a tarifa.'
            );
        }

        $data['organization_id'] = $organizationId;

        if (blank($data['code'] ?? null)) {
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

    $page = substr($page, 0, $pos) . $method . PHP_EOL . substr($page, $pos);
    writeChanged($rateCreate, $page, 'Código automático da tarifa');
}
