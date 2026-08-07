<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function replaceOrFail(string $path, string $search, string $replace, string $label): void
{
    $content = file_get_contents($path);

    if (str_contains($content, $replace)) {
        echo "[SEM ALTERACAO] {$label}" . PHP_EOL;
        return;
    }

    if (! str_contains($content, $search)) {
        throw new RuntimeException("Trecho nao encontrado: {$label}");
    }

    file_put_contents($path, str_replace($search, $replace, $content));
    echo "[CORRIGIDO] {$label}" . PHP_EOL;
}

$branchResource = $root . '/app/Filament/Resources/Branches/BranchResource.php';
$assetForm = $root . '/app/Filament/Resources/Assets/Schemas/AssetForm.php';
$assetTable = $root . '/app/Filament/Resources/Assets/Tables/AssetsTable.php';
$rateModel = $root . '/app/Models/RentalRatePlan.php';

replaceOrFail(
    $assetForm,
    "titleAttribute: 'trade_name'",
    "titleAttribute: 'name'",
    'Select de filial no cadastro do ativo'
);

replaceOrFail(
    $assetTable,
    "TextColumn::make('branch.trade_name')",
    "TextColumn::make('branch.name')",
    'Coluna filial na tabela de ativos'
);

replaceOrFail(
    $assetTable,
    "->relationship('branch', 'trade_name')",
    "->relationship('branch', 'name')",
    'Filtro filial na tabela de ativos'
);

$branchContent = file_get_contents($branchResource);
$branchContent = str_replace(
    "TextInput::make('code')->label('Código')->required()->maxLength(20),",
    "TextInput::make('code')->label('Código')->disabled()->dehydrated()->helperText('Gerado automaticamente.'),",
    $branchContent
);
$branchContent = str_replace(
    "TextInput::make('name')->label('Nome')->required()->maxLength(150),",
    "TextInput::make('name')->label('Nome da filial / cidade')->required()->maxLength(150)->helperText('Ex.: Aripuanã, Alta Floresta.'),",
    $branchContent
);
$branchContent = str_replace(
    "TextColumn::make('name')->label('Nome')",
    "TextColumn::make('name')->label('Filial')",
    $branchContent
);
file_put_contents($branchResource, $branchContent);
echo "[CORRIGIDO] Cadastro e listagem de filiais." . PHP_EOL;

$branchModel = $root . '/app/Models/Branch.php';
$modelContent = file_get_contents($branchModel);

if (! str_contains($modelContent, "key: 'branch'")) {
    $needle = "class Branch extends Model\n{";
    $insert = <<<'PHP'
class Branch extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $branch): void {
            if (blank($branch->code) && filled($branch->organization_id)) {
                $branch->code = app(\App\Services\Numbering\NumberSequenceService::class)->next(
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
    if (! str_contains($modelContent, $needle)) {
        throw new RuntimeException('Classe Branch nao localizada.');
    }
    $modelContent = str_replace($needle, $insert, $modelContent);
    file_put_contents($branchModel, $modelContent);
    echo "[CORRIGIDO] Código automático de filial." . PHP_EOL;
}

$rateContent = file_get_contents($rateModel);

if (! str_contains($rateContent, 'function assetCategory()')) {
    $needle = "class RentalRatePlan extends Model\n{";
    $insert = <<<'PHP'
class RentalRatePlan extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $rate): void {
            if (blank($rate->code) && filled($rate->organization_id)) {
                $rate->code = app(\App\Services\Numbering\NumberSequenceService::class)->next(
                    organizationId: $rate->organization_id,
                    key: 'rental_rate_plan',
                    name: 'Tarifas de locação',
                    prefix: 'TAR-',
                    padding: 4,
                );
            }
        });
    }

    public function assetCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AssetCategory::class, 'asset_category_id');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

PHP;
    if (! str_contains($rateContent, $needle)) {
        throw new RuntimeException('Classe RentalRatePlan nao localizada.');
    }
    $rateContent = str_replace($needle, $insert, $rateContent);
    file_put_contents($rateModel, $rateContent);
    echo "[CORRIGIDO] Relações e código automático das tarifas." . PHP_EOL;
}
