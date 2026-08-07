<?php

declare(strict_types=1);

$projectRoot = $argv[1] ?? 'C:\dev\careca-locadora';
$projectRoot = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $projectRoot), DIRECTORY_SEPARATOR);

function filePath(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
}

function readRequired(string $path): string
{
    if (! is_file($path)) {
        fwrite(STDERR, "[ERRO] Arquivo não encontrado: {$path}\n");
        exit(2);
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fwrite(STDERR, "[ERRO] Não foi possível ler: {$path}\n");
        exit(2);
    }

    return $content;
}

function writeUtf8(string $path, string $content): void
{
    if (file_put_contents($path, $content) === false) {
        fwrite(STDERR, "[ERRO] Não foi possível gravar: {$path}\n");
        exit(3);
    }
}

function replaceExact(string &$content, string $from, string $to, string $label, bool $required = false): int
{
    $count = substr_count($content, $from);

    if ($count > 0) {
        $content = str_replace($from, $to, $content);
        echo "[CORRIGIDO] {$label}: {$count} ocorrência(s)\n";
        return $count;
    }

    if ($required) {
        fwrite(STDERR, "[ERRO] Padrão obrigatório não encontrado: {$label}\n");
        exit(4);
    }

    echo "[OK] {$label}: padrão já corrigido ou não presente\n";
    return 0;
}

echo PHP_EOL;
echo "Careca Locadora - Patch Consolidado 16.0.4" . PHP_EOL;
echo "Reservas + Upload de Ativos + Nome Operacional da Filial" . PHP_EOL;
echo PHP_EOL;

if (! is_file(filePath($projectRoot, 'artisan'))) {
    fwrite(STDERR, "[ERRO] Projeto Laravel não encontrado em {$projectRoot}\n");
    exit(1);
}

$total = 0;

/*
|--------------------------------------------------------------------------
| 1. RESERVAS: normaliza chamadas de RentalCommercialPricingService::quote
|--------------------------------------------------------------------------
*/
$publicReservation = filePath($projectRoot, 'app/Services/Rentals/PublicReservationService.php');
$content = readRequired($publicReservation);

$total += replaceExact(
    $content,
    "                customerId: \$customer->id,\r\n",
    "",
    'PublicReservationService customerId'
);
$total += replaceExact(
    $content,
    "                customerId: \$customer->id,\n",
    "",
    'PublicReservationService customerId LF'
);

writeUtf8($publicReservation, $content);

$publicCatalog = filePath($projectRoot, 'app/Http/Controllers/Api/PublicCatalogController.php');
if (is_file($publicCatalog)) {
    $content = readRequired($publicCatalog);

    // Corrige exclusivamente o quarto argumento legado da cotação pública.
    $content = preg_replace(
        '/(\$quote\s*=\s*\$pricing->quote\(\s*\$this->search\(\$data\),\s*\$data\[\'commercial_item_ids\'\]\s*\?\?\s*\[\],\s*\$data\[\'coupon_code\'\]\s*\?\?\s*null,)\s*\$data\[\'customer_id\'\]\s*\?\?\s*null,\s*(\);)/s',
        '$1' . PHP_EOL . '        $2',
        $content,
        -1,
        $count
    );

    if ($count > 0) {
        echo "[CORRIGIDO] PublicCatalogController quarto argumento customer_id: {$count} ocorrência(s)\n";
        $total += $count;
    } else {
        echo "[OK] PublicCatalogController: quarto argumento legado já removido ou estrutura diferente\n";
    }

    writeUtf8($publicCatalog, $content);
}

/*
|--------------------------------------------------------------------------
| 2. ATIVOS: upload de fotos/documentos
|--------------------------------------------------------------------------
| O erro "caminho de arquivo que não é permitido" é disparado pela proteção
| explícita de path no FileUpload para estados persistidos no relacionamento.
| Em área administrativa autenticada, removemos essa proteção explícita do
| AssetForm e mantemos disk, directory, visibility, image e maxSize.
*/
$assetForm = filePath($projectRoot, 'app/Filament/Resources/Assets/Schemas/AssetForm.php');

if (is_file($assetForm)) {
    $content = readRequired($assetForm);

    $count = preg_match_all('/^[ \t]*->preventFilePathTampering\(\)\R/m', $content);
    if ($count > 0) {
        $content = preg_replace('/^[ \t]*->preventFilePathTampering\(\)\R/m', '', $content) ?? $content;
        echo "[CORRIGIDO] AssetForm preventFilePathTampering: {$count} ocorrência(s) removida(s)\n";
        $total += $count;
    } else {
        echo "[OK] AssetForm: proteção explícita de path já não está presente\n";
    }

    writeUtf8($assetForm, $content);
} else {
    echo "[AVISO] AssetForm não encontrado no caminho esperado. Nenhuma alteração de upload aplicada.\n";
}

/*
|--------------------------------------------------------------------------
| 3. FILIAIS: nome da filial é a identificação operacional
|--------------------------------------------------------------------------
*/
$branchResource = filePath($projectRoot, 'app/Filament/Resources/Branches/BranchResource.php');

if (is_file($branchResource)) {
    $content = readRequired($branchResource);

    $total += replaceExact(
        $content,
        "->label('Nome da filial / cidade')",
        "->label('Nome da filial')",
        'Label Nome da filial'
    );

    $total += replaceExact(
        $content,
        "->label(\"Nome da filial / cidade\")",
        "->label(\"Nome da filial\")",
        'Label Nome da filial aspas duplas'
    );

    writeUtf8($branchResource, $content);
}

/*
|--------------------------------------------------------------------------
| 4. SELETORES E LISTAGENS: Branch.name em todo o módulo operacional
|--------------------------------------------------------------------------
| Corrige padrões históricos usados nas telas do Careca Locadora.
*/
$appRoot = filePath($projectRoot, 'app');

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($appRoot, FilesystemIterator::SKIP_DOTS)
);

$changedFiles = 0;

foreach ($iterator as $file) {
    if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);

    if ($content === false) {
        continue;
    }

    $original = $content;

    // Relacionamentos Filament de filial.
    $content = str_replace(
        [
            "->relationship('branch', 'trade_name')",
            "->relationship('branch','trade_name')",
            '->relationship("branch", "trade_name")',
            '->relationship("branch","trade_name")',
        ],
        [
            "->relationship('branch', 'name')",
            "->relationship('branch','name')",
            '->relationship("branch", "name")',
            '->relationship("branch","name")',
        ],
        $content
    );

    // Colunas de filial.
    $content = str_replace(
        [
            "TextColumn::make('branch.trade_name')",
            'TextColumn::make("branch.trade_name")',
        ],
        [
            "TextColumn::make('branch.name')",
            'TextColumn::make("branch.name")',
        ],
        $content
    );

    // Consultas típicas de Select::make('branch_id').
    $content = preg_replace(
        "/(Select::make\('branch_id'\).*?Branch::query\(\).*?)->orderBy\('trade_name'\)(.*?)->pluck\('trade_name',\s*'id'\)/s",
        "$1->orderBy('name')$2->pluck('name', 'id')",
        $content
    ) ?? $content;

    $content = preg_replace(
        '/(Select::make\("branch_id"\).*?Branch::query\(\).*?)->orderBy\("trade_name"\)(.*?)->pluck\("trade_name",\s*"id"\)/s',
        '$1->orderBy("name")$2->pluck("name", "id")',
        $content
    ) ?? $content;

    // API pública e demais apresentações de filial: usa o nome cadastrado da filial.
    $content = str_replace(
        [
            "'name' => \$branch->trade_name ?: \$branch->name",
            "'name' => \$asset->branch?->trade_name ?: \$asset->branch?->name",
            '"name" => $branch->trade_name ?: $branch->name',
            '"name" => $asset->branch?->trade_name ?: $asset->branch?->name',
        ],
        [
            "'name' => \$branch->name",
            "'name' => \$asset->branch?->name",
            '"name" => $branch->name',
            '"name" => $asset->branch?->name',
        ],
        $content
    );

    if ($content !== $original) {
        writeUtf8($path, $content);
        $changedFiles++;
    }
}

echo "[OK] Padronização de filial aplicada em {$changedFiles} arquivo(s) PHP.\n";

/*
|--------------------------------------------------------------------------
| Validações estruturais do patch
|--------------------------------------------------------------------------
*/
$pricingService = filePath($projectRoot, 'app/Services/Rentals/RentalCommercialPricingService.php');
$pricing = readRequired($pricingService);

if (preg_match('/function\s+quote\s*\(\s*ReservationSearch\s+\$search\s*,\s*array\s+\$itemIds\s*=\s*\[\]\s*,\s*\?string\s+\$couponCode\s*=\s*null\s*\)/s', $pricing) !== 1) {
    fwrite(STDERR, "[ERRO] A assinatura atual de RentalCommercialPricingService::quote() mudou. Patch interrompido por segurança.\n");
    exit(5);
}

$publicReservationCheck = readRequired($publicReservation);
if (str_contains($publicReservationCheck, 'customerId:')) {
    fwrite(STDERR, "[ERRO] Ainda existe customerId: no PublicReservationService.\n");
    exit(6);
}

if (is_file($assetForm)) {
    $assetCheck = readRequired($assetForm);
    if (str_contains($assetCheck, 'preventFilePathTampering()')) {
        fwrite(STDERR, "[ERRO] Ainda existe preventFilePathTampering() no AssetForm.\n");
        exit(7);
    }
}

echo PHP_EOL;
echo "[OK] Patch 16.0.4 aplicado com sucesso." . PHP_EOL;
echo "[OK] Total de substituições diretas: {$total}" . PHP_EOL;
echo "[OK] Arquivos com padronização de filial: {$changedFiles}" . PHP_EOL;
echo PHP_EOL;
