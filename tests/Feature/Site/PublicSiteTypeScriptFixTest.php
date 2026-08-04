<?php

it('corrige a precedência entre nullish e OR na home', function (): void {
    $page = file_get_contents(resource_path('js/pages/welcome.tsx'));

    expect($page)
        ->not->toContain('?? form.branch_id || null')
        ->not->toContain('?? filters.branch_id || null');
});

it('remove o import inexistente de register no login', function (): void {
    $login = file_get_contents(resource_path('js/pages/auth/login.tsx'));

    expect($login)
        ->not->toContain("import { register } from '@/routes'")
        ->not->toContain('href={register()}')
        ->not->toContain('href={register.url()}');
});
