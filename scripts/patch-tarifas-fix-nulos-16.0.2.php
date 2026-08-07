<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

$model = $root . '/app/Models/RentalRatePlan.php';
$resource = $root . '/app/Filament/Resources/RentalRatePlans/RentalRatePlanResource.php';

foreach ([$model, $resource] as $path) {
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }
}

/*
|--------------------------------------------------------------------------
| Model: defaults + normalização defensiva
|--------------------------------------------------------------------------
*/

$modelContent = file_get_contents($model);

$oldAttributes = "protected \$attributes=['billing_unit'=>'daily','unit_value'=>0,'deposit_value'=>0,'minimum_value'=>0,'minimum_quantity'=>1,'priority'=>100,'status'=>'active'];";
$newAttributes = "protected \$attributes=['billing_unit'=>'daily','unit_value'=>0,'included_distance'=>null,'included_hours'=>null,'extra_distance_value'=>0,'extra_hour_value'=>0,'deposit_value'=>0,'minimum_value'=>0,'minimum_quantity'=>1,'priority'=>100,'status'=>'active'];";

if (str_contains($modelContent, $oldAttributes)) {
    $modelContent = str_replace($oldAttributes, $newAttributes, $modelContent);
    echo "[CORRIGIDO] Defaults do RentalRatePlan atualizados." . PHP_EOL;
} elseif (str_contains($modelContent, "'extra_distance_value'=>0")) {
    echo "[SEM ALTERACAO] Defaults numericos ja existem no RentalRatePlan." . PHP_EOL;
} else {
    throw new RuntimeException(
        'Bloco de atributos padrão do RentalRatePlan não localizado.'
    );
}

if (! str_contains($modelContent, 'normalizeRequiredNumericFields')) {
    $position = strrpos($modelContent, '}');

    if ($position === false) {
        throw new RuntimeException(
            'Fechamento da classe RentalRatePlan nao localizado.'
        );
    }

    $methods = <<<'PHP'

    protected static function booted(): void
    {
        static::saving(function (self $rate): void {
            $rate->normalizeRequiredNumericFields();
        });
    }

    private function normalizeRequiredNumericFields(): void
    {
        foreach ([
            'unit_value',
            'extra_distance_value',
            'extra_hour_value',
            'deposit_value',
            'minimum_value',
        ] as $field) {
            if ($this->getAttribute($field) === null || $this->getAttribute($field) === '') {
                $this->setAttribute($field, 0);
            }
        }

        if ($this->getAttribute('minimum_quantity') === null || $this->getAttribute('minimum_quantity') === '') {
            $this->setAttribute('minimum_quantity', 1);
        }

        if ($this->getAttribute('priority') === null || $this->getAttribute('priority') === '') {
            $this->setAttribute('priority', 100);
        }
    }
PHP;

    $modelContent =
        substr($modelContent, 0, $position)
        . $methods
        . PHP_EOL
        . substr($modelContent, $position);

    echo "[CORRIGIDO] Normalizacao defensiva adicionada ao RentalRatePlan." . PHP_EOL;
}

file_put_contents($model, $modelContent);

/*
|--------------------------------------------------------------------------
| Resource: campos não nulos nunca enviam null
|--------------------------------------------------------------------------
*/

$resourceContent = file_get_contents($resource);

$replacements = [
    "TextInput::make('extra_distance_value')\n                        ->label('KM excedente')\n                        ->numeric()\n                        ->prefix('R$')," =>
    "TextInput::make('extra_distance_value')\n                        ->label('KM excedente')\n                        ->numeric()\n                        ->prefix('R$')\n                        ->default(0)\n                        ->required(),",

    "TextInput::make('deposit_value')\n                        ->label('Caução')\n                        ->numeric()\n                        ->prefix('R$')\n                        ->default(0)\n                        ->required()," =>
    "TextInput::make('deposit_value')\n                        ->label('Caução')\n                        ->numeric()\n                        ->prefix('R$')\n                        ->default(0)\n                        ->required(),",
];

foreach ($replacements as $search => $replace) {
    if (str_contains($resourceContent, $search)) {
        $resourceContent = str_replace($search, $replace, $resourceContent);
    }
}

if (! str_contains($resourceContent, "TextInput::make('extra_hour_value')")) {
    $anchor = "DatePicker::make('valid_from')";

    if (str_contains($resourceContent, $anchor)) {
        $fields = <<<'PHP'
TextInput::make('included_hours')
                        ->label('Horas incluídas')
                        ->numeric()
                        ->suffix(' h'),

                    TextInput::make('extra_hour_value')
                        ->label('Hora excedente')
                        ->numeric()
                        ->prefix('R$')
                        ->default(0)
                        ->required(),


PHP;
        $resourceContent = str_replace($anchor, $fields . $anchor, $resourceContent);
        echo "[CORRIGIDO] Campos de hora incluída/excedente adicionados." . PHP_EOL;
    }
}

if (! str_contains($resourceContent, "->label('KM excedente')\n                        ->numeric()\n                        ->prefix('R$')\n                        ->default(0)\n                        ->required()")) {
    throw new RuntimeException(
        'Campo KM excedente nao ficou com default 0 e required.'
    );
}

file_put_contents($resource, $resourceContent);

echo "[CORRIGIDO] Formulario de tarifas protegido contra valores nulos." . PHP_EOL;
