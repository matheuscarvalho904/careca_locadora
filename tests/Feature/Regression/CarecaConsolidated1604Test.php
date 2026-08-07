<?php

it('mantém a cotação comercial com os três parâmetros reais', function (): void {
    $service = file_get_contents(app_path('Services/Rentals/RentalCommercialPricingService.php'));
    $public = file_get_contents(app_path('Services/Rentals/PublicReservationService.php'));

    expect($service)
        ->toContain('function quote(')
        ->toContain('ReservationSearch $search')
        ->toContain('array $itemIds=[]')
        ->toContain('?string $couponCode=null');

    expect($public)
        ->not->toContain('customerId:');
});

it('não envia customer_id como quarto argumento na cotação pública', function (): void {
    $path = app_path('Http/Controllers/Api/PublicCatalogController.php');

    if (! file_exists($path)) {
        $this->markTestSkipped('PublicCatalogController não existe neste build.');
    }

    $controller = file_get_contents($path);

    expect($controller)->not->toMatch(
        '/\\\\(.*?\\\\[\'customer_id\'\\]\\s*\\?\\?\\s*null,/s'
    );
});

it('permite upload de fotos do ativo sem bloqueio explícito do caminho persistido', function (): void {
    $path = app_path('Filament/Resources/Assets/Schemas/AssetForm.php');

    if (! file_exists($path)) {
        $this->markTestSkipped('AssetForm não existe neste build.');
    }

    $form = file_get_contents($path);

    expect($form)
        ->toContain("directory('fleet/photos')")
        ->toContain("disk('public')")
        ->not->toContain('preventFilePathTampering()');
});

it('usa nome da filial como identificação operacional', function (): void {
    $branchPath = app_path('Filament/Resources/Branches/BranchResource.php');

    if (file_exists($branchPath)) {
        $branch = file_get_contents($branchPath);

        expect($branch)
            ->toContain('Nome da filial')
            ->not->toContain('Nome da filial / cidade');
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS)
    );

    $legacy = [];

    foreach ($iterator as $file) {
        if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if (
            str_contains($content, "relationship('branch', 'trade_name')")
            || str_contains($content, "TextColumn::make('branch.trade_name')")
        ) {
            $legacy[] = $file->getPathname();
        }
    }

    expect($legacy)->toBe([]);
});
