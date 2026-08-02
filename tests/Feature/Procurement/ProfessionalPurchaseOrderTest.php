<?php

it('adiciona pesquisa inteligente e condições financeiras em OC e OS', function (): void {
    $purchase = file_get_contents(
        app_path('Filament/Resources/PurchaseOrders/PurchaseOrderResource.php')
    );

    $service = file_get_contents(
        app_path('Filament/Resources/ServiceOrders/ServiceOrderResource.php')
    );

    $support = file_get_contents(
        app_path('Filament/Support/ProcurementSelectOptions.php')
    );

    foreach ([$purchase, $service] as $resource) {
        expect($resource)
            ->toContain("->searchable(['prefix', 'plate', 'name'])")
            ->toContain('->preload()')
            ->toContain("Select::make('payment_condition_id')")
            ->toContain("->relationship('paymentCondition', 'name')")
            ->toContain("->searchable(['code', 'name'])")
            ->toContain("DatePicker::make('first_due_date')")
            ->toContain("TextInput::make('installments')")
            ->toContain("TextInput::make('installment_interval_days')");
    }

    expect($purchase)
        ->toContain("TextInput::make('delivery_location')")
        ->toContain("Textarea::make('supplier_notes')")
        ->toContain("Textarea::make('internal_notes')");

    expect($support)
        ->toContain("->where('prefix', 'ilike'")
        ->toContain("->orWhere('plate', 'ilike'")
        ->toContain("->orWhere('name', 'ilike'")
        ->toContain('public static function assetLabel');
});
