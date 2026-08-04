<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function save(string $path, string $content): void
{
    file_put_contents($path, $content);
    echo "[CORRIGIDO] {$path}" . PHP_EOL;
}

$welcome = $root . '/resources/js/pages/welcome.tsx';

if (! file_exists($welcome)) {
    throw new RuntimeException("Arquivo nao encontrado: {$welcome}");
}

$welcomeContent = file_get_contents($welcome);

$welcomeContent = str_replace(
    'vehicle.branch?.id ?? form.branch_id || null',
    '(vehicle.branch?.id ?? form.branch_id) || null',
    $welcomeContent
);

$welcomeContent = str_replace(
    'vehicle.branch?.id ?? filters.branch_id || null',
    '(vehicle.branch?.id ?? filters.branch_id) || null',
    $welcomeContent
);

save($welcome, $welcomeContent);

$login = $root . '/resources/js/pages/auth/login.tsx';

if (! file_exists($login)) {
    throw new RuntimeException("Arquivo nao encontrado: {$login}");
}

$loginContent = file_get_contents($login);

$loginContent = preg_replace_callback(
    '/import\s*\{([^}]*)\}\s*from\s*[\'"]@\/routes[\'"];/m',
    function (array $matches): string {
        $members = array_values(array_filter(
            array_map(
                static fn (string $member): string => trim($member),
                explode(',', $matches[1])
            ),
            static fn (string $member): bool =>
                $member !== '' && $member !== 'register'
        ));

        if ($members === []) {
            return '';
        }

        return "import { " . implode(', ', $members) . " } from '@/routes';";
    },
    $loginContent
);

$loginContent = str_replace(
    ['href={register()}', 'href={register.url()}'],
    ['href="/register"', 'href="/register"'],
    $loginContent
);

save($login, $loginContent);
