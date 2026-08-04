<?php

use App\Services\Rentals\RentalAvailabilityService;

it('define todos os status operacionais que bloqueiam disponibilidade', function (): void {
    expect(RentalAvailabilityService::BLOCKING_STATUSES)
        ->toContain('confirmed')
        ->toContain('converted')
        ->toContain('in_rental')
        ->toContain('rented');
});

it('corrige os resources quebrados de bancos e certificados', function (): void {
    $bank = file_get_contents(
        app_path('Filament/Resources/BankAccounts/BankAccountResource.php')
    );

    $certificate = file_get_contents(
        app_path('Filament/Resources/DigitalCertificates/DigitalCertificateResource.php')
    );

    expect($bank)
        ->toContain('Utilities\Get')
        ->toContain("visible(fn (Get \$get)")
        ->and($certificate)
        ->toContain('Utilities\Get')
        ->toContain("visible(fn (Get \$get)");
});

it('adiciona cadastro inteligente e bancos no fornecedor', function (): void {
    $company = file_get_contents(
        app_path('Filament/Resources/Companies/CompanyResource.php')
    );

    $branch = file_get_contents(
        app_path('Filament/Resources/Branches/BranchResource.php')
    );

    $partner = file_get_contents(
        app_path('Filament/Resources/BusinessPartners/Schemas/BusinessPartnerForm.php')
    );

    expect($company)
        ->toContain('CnpjLookupService')
        ->toContain('CepLookupService')
        ->and($branch)
        ->toContain('CnpjLookupService')
        ->toContain('CepLookupService')
        ->and($partner)
        ->toContain("Repeater::make('bankAccounts')");
});
