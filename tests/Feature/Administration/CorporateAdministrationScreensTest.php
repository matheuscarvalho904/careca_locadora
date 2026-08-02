<?php
it('registra as telas de empresas filiais e usuários', function (): void {
    expect(file_exists(app_path('Filament/Resources/Companies/CompanyResource.php')))->toBeTrue()
        ->and(file_exists(app_path('Filament/Resources/Branches/BranchResource.php')))->toBeTrue()
        ->and(file_exists(app_path('Filament/Resources/Users/UserResource.php')))->toBeTrue();
});
