<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);
$form = $root . '/app/Filament/Resources/Assets/Schemas/AssetForm.php';
$table = $root . '/app/Filament/Resources/Assets/Tables/AssetsTable.php';

foreach ([$form, $table] as $path) {
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }
}

$formContent = file_get_contents($form);

if (! str_contains($formContent, "Select::make('branch_id')")) {
    $anchor = "TextInput::make('name')";

    if (! str_contains($formContent, $anchor)) {
        throw new RuntimeException("Campo nome nao encontrado no AssetForm.");
    }

    $field = <<<'PHP'
Select::make('branch_id')
                            ->label('Filial responsável')
                            ->relationship(
                                name: 'branch',
                                titleAttribute: 'trade_name',
                                modifyQueryUsing: fn (Builder $query): Builder =>
                                    $query
                                        ->where('status', 'active')
                                        ->orderBy('trade_name')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText(
                                'Define em qual loja o veículo ficará disponível para reservas.'
                            ),


PHP;

    file_put_contents($form, str_replace($anchor, $field . $anchor, $formContent));
    echo "[CORRIGIDO] Campo filial adicionado ao cadastro." . PHP_EOL;
} else {
    echo "[SEM ALTERACAO] Campo filial ja existe." . PHP_EOL;
}

$tableContent = file_get_contents($table);

if (! str_contains($tableContent, "TextColumn::make('branch.trade_name')")) {
    $anchor = "TextColumn::make('plate')";

    $column = <<<'PHP'
TextColumn::make('branch.trade_name')
                    ->label('Filial')
                    ->placeholder('Sem filial')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),


PHP;

    if (! str_contains($tableContent, $anchor)) {
        throw new RuntimeException("Coluna placa nao encontrada.");
    }

    $tableContent = str_replace($anchor, $column . $anchor, $tableContent);
}

if (! str_contains($tableContent, "SelectFilter::make('branch_id')")) {
    $anchor = "SelectFilter::make('category_id')";

    $filter = <<<'PHP'
SelectFilter::make('branch_id')
                    ->label('Filial')
                    ->relationship('branch', 'trade_name')
                    ->searchable()
                    ->preload(),


PHP;

    if (! str_contains($tableContent, $anchor)) {
        throw new RuntimeException("Filtro categoria nao encontrado.");
    }

    $tableContent = str_replace($anchor, $filter . $anchor, $tableContent);
}

file_put_contents($table, $tableContent);
echo "[CORRIGIDO] Listagem e filtro de filial atualizados." . PHP_EOL;
