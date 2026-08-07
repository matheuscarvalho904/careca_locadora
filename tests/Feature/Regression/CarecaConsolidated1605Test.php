<?php

it('mantém reserva pública compatível com a assinatura real da cotação', function (): void {
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
        '/\$pricing->quote\(.*?\$data\[\'customer_id\'\]\s*\?\?\s*null,/s'
    );
});

it('mantém upload de fotos do ativo sem bloqueio explícito de caminho', function (): void {
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

it('usa nome da filial como identificação operacional, separado da cidade', function (): void {
    $path = app_path('Filament/Resources/Branches/BranchResource.php');

    if (! file_exists($path)) {
        $this->markTestSkipped('BranchResource não existe neste build.');
    }

    $resource = file_get_contents($path);

    expect($resource)
        ->toContain("TextInput::make('name')->label('Nome da filial')")
        ->toContain("TextInput::make('city')->label('Cidade')")
        ->not->toContain('Nome da filial / cidade')
        ->toContain('Matriz, Pátio Aripuanã, Base Alta Floresta, Unidade Centro');
});
