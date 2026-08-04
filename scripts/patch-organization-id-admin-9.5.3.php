<?php

$root = dirname(__DIR__);

$targets = [
    $root . '/app/Filament/Resources/Companies/CompanyResource.php',
    $root . '/app/Filament/Resources/Branches/BranchResource.php',
];

foreach ($targets as $file) {
    $content = file_get_contents($file);

    if (! str_contains($content, 'use Filament\Forms\Components\Hidden;')) {
        $content = str_replace(
            'use Filament\Forms\Components\FileUpload;',
            "use Filament\\Forms\\Components\\FileUpload;\nuse Filament\\Forms\\Components\\Hidden;",
            $content
        );

        if (! str_contains($content, 'use Filament\Forms\Components\FileUpload;')) {
            $content = str_replace(
                'use Filament\Forms\Components\DatePicker;',
                "use Filament\\Forms\\Components\\DatePicker;\nuse Filament\\Forms\\Components\\Hidden;",
                $content
            );
        }
    }

    if (! str_contains($content, "Hidden::make('organization_id')")) {
        $needle = "Section::make(";
        $position = strpos($content, $needle);

        if ($position === false) {
            fwrite(STDERR, "Section não encontrada em {$file}.\n");
            exit(1);
        }

        $schemaNeedle = "->schema([";
        $schemaPosition = strpos($content, $schemaNeedle, $position);

        if ($schemaPosition === false) {
            fwrite(STDERR, "Schema não encontrado em {$file}.\n");
            exit(1);
        }

        $insertAt = $schemaPosition + strlen($schemaNeedle);

        $hidden = <<<'PHP'

                Hidden::make('organization_id')
                    ->default(fn (): ?string => auth()->user()?->organization_id)
                    ->dehydrated()
                    ->required(),

PHP;

        $content = substr_replace($content, $hidden, $insertAt, 0);
    }

    file_put_contents($file, $content);
}
