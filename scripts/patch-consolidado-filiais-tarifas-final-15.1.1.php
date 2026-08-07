<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);
$branchResource = $root . '/app/Filament/Resources/Branches/BranchResource.php';

if (! file_exists($branchResource)) {
    throw new RuntimeException("Arquivo nao encontrado: {$branchResource}");
}

$content = file_get_contents($branchResource);
$original = $content;

$patterns = [
    "TextInput::make('name')->label('Nome da filial')->required()->maxLength(150),",
    "TextInput::make('name')\n                    ->label('Nome da filial')",
    "TextInput::make('name')\r\n                    ->label('Nome da filial')",
];

$replacements = [
    "TextInput::make('name')->label('Nome da filial / cidade')->required()->maxLength(150)->helperText('Ex.: Aripuanã, Alta Floresta.'),",
    "TextInput::make('name')\n                    ->label('Nome da filial / cidade')",
    "TextInput::make('name')\r\n                    ->label('Nome da filial / cidade')",
];

$content = str_replace($patterns, $replacements, $content);

if (! str_contains($content, 'Nome da filial / cidade')) {
    throw new RuntimeException(
        'Nao foi possivel atualizar o label da filial.'
    );
}

if (! str_contains($content, "helperText('Ex.: Aripuanã, Alta Floresta.')")) {
    $content = str_replace(
        "->label('Nome da filial / cidade')->required()->maxLength(150),",
        "->label('Nome da filial / cidade')->required()->maxLength(150)->helperText('Ex.: Aripuanã, Alta Floresta.'),",
        $content
    );
}

if ($content !== $original) {
    file_put_contents($branchResource, $content);
    echo "[CORRIGIDO] Nome operacional da filial atualizado." . PHP_EOL;
} else {
    echo "[SEM ALTERACAO] Nome operacional da filial ja esta correto." . PHP_EOL;
}
