<?php

declare(strict_types=1);

$projectRoot = $argv[1] ?? 'C:\dev\careca-locadora';
$projectRoot = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $projectRoot), DIRECTORY_SEPARATOR);

function p(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
}

function readFileRequired(string $path): string
{
    if (! is_file($path)) {
        fwrite(STDERR, "[ERRO] Arquivo não encontrado: {$path}\n");
        exit(2);
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fwrite(STDERR, "[ERRO] Falha ao ler: {$path}\n");
        exit(2);
    }

    return $content;
}

function writeFileSafe(string $path, string $content): void
{
    if (file_put_contents($path, $content) === false) {
        fwrite(STDERR, "[ERRO] Falha ao gravar: {$path}\n");
        exit(3);
    }
}

echo PHP_EOL;
echo "Careca Locadora - Validação Final 16.0.5" . PHP_EOL;
echo "Ajuste de testes + nomenclatura operacional de filial" . PHP_EOL;
echo PHP_EOL;

if (! is_file(p($projectRoot, 'artisan'))) {
    fwrite(STDERR, "[ERRO] Projeto Laravel não encontrado: {$projectRoot}\n");
    exit(1);
}

/*
|--------------------------------------------------------------------------
| 1. Corrige teste novo 16.0.4 (interpolação indevida de $pricing no regex)
|--------------------------------------------------------------------------
*/
$regressionTest = p($projectRoot, 'tests/Feature/Regression/CarecaConsolidated1604Test.php');

if (is_file($regressionTest)) {
    $content = readFileRequired($regressionTest);

    $old = '        "/\\\\$pricing->quote\\\\(.*?\\\\$data\\\\[\'customer_id\'\\\\]\\\\s*\\\\?\\\\?\\\\s*null,/s"';
    $new = "        '/\\\\$pricing->quote\\\\(.*?\\\\$data\\\\[\\'customer_id\\'\\\\]\\\\s*\\\\?\\\\?\\\\s*null,/s'";

    if (str_contains($content, $old)) {
        $content = str_replace($old, $new, $content);
        echo "[CORRIGIDO] Regex do teste CarecaConsolidated1604Test\n";
    } else {
        // Fallback mais tolerante.
        $content = preg_replace(
            '/expect\(\$controller\)->not->toMatch\(\s*"\/\\\\\\\\\$pricing->quote.*?\/s"\s*\);/s',
            "expect(\$controller)->not->toMatch(\n        '/\\\\$pricing->quote\\\\(.*?\\\\$data\\\\[\\'customer_id\\'\\\\]\\\\s*\\\\?\\\\?\\\\s*null,/s'\n    );",
            $content
        ) ?? $content;

        echo "[OK] Teste 16.0.4 verificado/normalizado\n";
    }

    writeFileSafe($regressionTest, $content);
}

/*
|--------------------------------------------------------------------------
| 2. Atualiza teste legado de filial ao requisito atual
|--------------------------------------------------------------------------
*/
$branchTest = p($projectRoot, 'tests/Feature/Branches/BranchConsolidatedTest.php');

if (is_file($branchTest)) {
    $content = readFileRequired($branchTest);

    $count = substr_count($content, 'Nome da filial / cidade');
    if ($count > 0) {
        $content = str_replace('Nome da filial / cidade', 'Nome da filial', $content);
        echo "[CORRIGIDO] BranchConsolidatedTest: {$count} expectativa(s) atualizada(s)\n";
    } else {
        echo "[OK] BranchConsolidatedTest já usa Nome da filial\n";
    }

    writeFileSafe($branchTest, $content);
}

/*
|--------------------------------------------------------------------------
| 3. Remove orientação antiga de usar cidade como nome da filial
|--------------------------------------------------------------------------
*/
$branchResource = p($projectRoot, 'app/Filament/Resources/Branches/BranchResource.php');

if (is_file($branchResource)) {
    $content = readFileRequired($branchResource);

    $content = str_replace(
        "->helperText('Ex.: Aripuanã, Alta Floresta.')",
        "->helperText('Ex.: Matriz, Pátio Aripuanã, Base Alta Floresta, Unidade Centro.')",
        $content
    );

    $content = str_replace(
        '->helperText("Ex.: Aripuanã, Alta Floresta.")',
        '->helperText("Ex.: Matriz, Pátio Aripuanã, Base Alta Floresta, Unidade Centro.")',
        $content
    );

    writeFileSafe($branchResource, $content);
    echo "[CORRIGIDO] Ajuda do campo Nome da filial ajustada ao conceito operacional\n";
}

/*
|--------------------------------------------------------------------------
| 4. Validações de negócio efetivamente aplicadas pelo 16.0.4
|--------------------------------------------------------------------------
*/
$publicReservation = readFileRequired(p($projectRoot, 'app/Services/Rentals/PublicReservationService.php'));
if (str_contains($publicReservation, 'customerId:')) {
    fwrite(STDERR, "[ERRO] customerId: ainda existe em PublicReservationService.\n");
    exit(10);
}
echo "[OK] Reserva: customerId nomeado não existe mais\n";

$publicCatalogPath = p($projectRoot, 'app/Http/Controllers/Api/PublicCatalogController.php');
if (is_file($publicCatalogPath)) {
    $publicCatalog = readFileRequired($publicCatalogPath);

    if (preg_match('/\$pricing->quote\(\s*\$this->search\(\$data\),\s*\$data\[\'commercial_item_ids\'\]\s*\?\?\s*\[\],\s*\$data\[\'coupon_code\'\]\s*\?\?\s*null,\s*\$data\[\'customer_id\'\]/s', $publicCatalog)) {
        fwrite(STDERR, "[ERRO] PublicCatalogController ainda envia quarto argumento customer_id.\n");
        exit(11);
    }

    echo "[OK] API pública: cotação sem quarto argumento legado\n";
}

$assetFormPath = p($projectRoot, 'app/Filament/Resources/Assets/Schemas/AssetForm.php');
if (is_file($assetFormPath)) {
    $assetForm = readFileRequired($assetFormPath);

    if (str_contains($assetForm, 'preventFilePathTampering()')) {
        fwrite(STDERR, "[ERRO] AssetForm ainda contém preventFilePathTampering().\n");
        exit(12);
    }

    if (! str_contains($assetForm, "directory('fleet/photos')")) {
        fwrite(STDERR, "[ERRO] Configuração de fotos do ativo não localizada.\n");
        exit(13);
    }

    echo "[OK] Ativos: upload de fotos sem bloqueio explícito de caminho\n";
}

if (is_file($branchResource)) {
    $branch = readFileRequired($branchResource);

    if (! str_contains($branch, "TextInput::make('name')->label('Nome da filial')")) {
        fwrite(STDERR, "[ERRO] Campo Nome da filial não está no padrão esperado.\n");
        exit(14);
    }

    if (str_contains($branch, 'Nome da filial / cidade')) {
        fwrite(STDERR, "[ERRO] Nomenclatura antiga Nome da filial / cidade ainda existe.\n");
        exit(15);
    }

    echo "[OK] Filiais: Nome da filial é o identificador operacional\n";
}

echo PHP_EOL;
echo "[OK] Validação final 16.0.5 concluída." . PHP_EOL;
