<?php

it('preenche organization_id automaticamente nas telas de empresa e filial', function (): void {
    $company = file_get_contents(
        app_path('Filament/Resources/Companies/CompanyResource.php')
    );

    $branch = file_get_contents(
        app_path('Filament/Resources/Branches/BranchResource.php')
    );

    foreach ([$company, $branch] as $resource) {
        expect($resource)
            ->toContain("Hidden::make('organization_id')")
            ->toContain("auth()->user()?->organization_id")
            ->toContain('->dehydrated()')
            ->toContain('->required()');
    }
});

it('registra proteção adicional por observer', function (): void {
    $provider = file_get_contents(
        app_path('Providers/AppServiceProvider.php')
    );

    expect($provider)
        ->toContain('Company::observe(CompanyObserver::class)')
        ->toContain('Branch::observe(BranchObserver::class)');
});
