<?php

it('não possui chamada com parâmetro nomeado selectedItemIds', function (): void {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            app_path(),
            FilesystemIterator::SKIP_DOTS
        )
    );

    $matches = [];

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = file_get_contents($file->getPathname());

        if (str_contains($source, 'selectedItemIds:')) {
            $matches[] = $file->getPathname();
        }
    }

    expect($matches)->toBe([]);
});

it('mantém a assinatura real do motor comercial com itemIds', function (): void {
    $service = file_get_contents(
        app_path(
            'Services/Rentals/RentalCommercialPricingService.php'
        )
    );

    expect($service)
        ->toContain('function quote(')
        ->toContain('array $itemIds=[]');
});
