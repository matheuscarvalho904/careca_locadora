<?php

$root = dirname(__DIR__);
$file = $root . '/app/Providers/AppServiceProvider.php';
$content = file_get_contents($file);

if (! str_contains($content, 'use App\Models\Company;')) {
    $content = str_replace(
        'namespace App\Providers;',
        "namespace App\\Providers;\n\nuse App\\Models\\Branch;\nuse App\\Models\\Company;\nuse App\\Observers\\BranchObserver;\nuse App\\Observers\\CompanyObserver;",
        $content
    );
}

if (! str_contains($content, 'Company::observe(CompanyObserver::class);')) {
    $needle = 'public function boot(): void';
    $pos = strpos($content, $needle);

    if ($pos === false) {
        fwrite(STDERR, "Método boot não encontrado no AppServiceProvider.\n");
        exit(1);
    }

    $brace = strpos($content, '{', $pos);

    if ($brace === false) {
        fwrite(STDERR, "Abertura do método boot não encontrada.\n");
        exit(1);
    }

    $insert = <<<'PHP'

        Company::observe(CompanyObserver::class);
        Branch::observe(BranchObserver::class);
PHP;

    $content = substr_replace($content, $insert, $brace + 1, 0);
}

file_put_contents($file, $content);
