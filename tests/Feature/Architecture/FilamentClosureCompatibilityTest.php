<?php

it('não possui closures com parâmetro genérico $s no Filament', function (): void {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path('Filament'), FilesystemIterator::SKIP_DOTS)
    );

    $violations = [];

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if (preg_match('/\bfn\s*\([^)]*\$s\b|\bfunction\s*\([^)]*\$s\b/', $content)) {
            $violations[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
        }
    }

    expect($violations)->toBeEmpty(
        'Closures incompatíveis encontradas: ' . implode(', ', $violations)
    );
});
