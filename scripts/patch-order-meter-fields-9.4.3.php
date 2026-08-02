<?php

$root = dirname(__DIR__);

$files = [
    $root . '/app/Filament/Resources/PurchaseOrders/PurchaseOrderResource.php',
    $root . '/app/Filament/Resources/ServiceOrders/ServiceOrderResource.php',
];

$needle = <<<'PHP'
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),
PHP;

$meterFields = <<<'PHP'
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),

                            Select::make('meter_type')
                                ->label('Tipo de medidor')
                                ->options([
                                    'odometer' => 'Hodômetro',
                                    'hourmeter' => 'Horímetro',
                                ])
                                ->required(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),

                            TextInput::make('meter_reading')
                                ->label('Leitura atual')
                                ->numeric()
                                ->minValue(0)
                                ->suffix(fn ($get): ?string => match ($get('meter_type')) {
                                    'odometer' => 'km',
                                    'hourmeter' => 'h',
                                    default => null,
                                })
                                ->required(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),

                            \Filament\Forms\Components\DateTimePicker::make('meter_recorded_at')
                                ->label('Data e hora da leitura')
                                ->seconds(false)
                                ->default(now())
                                ->required(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                )
                                ->visible(fn ($get): bool =>
                                    $get('application_type') === 'asset'
                                ),
PHP;

foreach ($files as $file) {
    $content = file_get_contents($file);

    if (str_contains($content, "Select::make('meter_type')")) {
        fwrite(STDOUT, basename($file) . ": campos já existentes.\n");
        continue;
    }

    $position = strpos($content, $needle);

    if ($position === false) {
        fwrite(STDERR, "Bloco do ativo não encontrado em {$file}.\n");
        exit(1);
    }

    $content = substr_replace(
        $content,
        $meterFields,
        $position,
        strlen($needle),
    );

    file_put_contents($file, $content);

    fwrite(STDOUT, basename($file) . ": campos de medidor adicionados.\n");
}
