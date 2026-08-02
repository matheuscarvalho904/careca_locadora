<?php

it('não consulta display_name como coluna e traduz status das ordens', function (): void {
    $purchase = file_get_contents(
        app_path('Filament/Resources/PurchaseOrders/PurchaseOrderResource.php')
    );

    $service = file_get_contents(
        app_path('Filament/Resources/ServiceOrders/ServiceOrderResource.php')
    );

    foreach ([$purchase, $service] as $resource) {
        expect($resource)
            ->not->toContain("->orderBy('display_name')")
            ->not->toContain("->pluck('display_name', 'id')")
            ->toContain("->orderBy('legal_name')")
            ->toContain('$partner->display_name')
            ->toContain("'draft' => 'Rascunho'")
            ->toContain("payment_condition_id");
    }
});
