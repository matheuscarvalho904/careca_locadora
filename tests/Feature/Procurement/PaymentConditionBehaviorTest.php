<?php

use App\Models\PaymentCondition;
use App\Models\PurchaseOrder;
use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('mantém os relacionamentos de condição de pagamento em OC e OS', function (): void {
    expect((new PurchaseOrder())->paymentCondition())
        ->toBeInstanceOf(BelongsTo::class);

    expect((new ServiceOrder())->paymentCondition())
        ->toBeInstanceOf(BelongsTo::class);
});

it('mantém os campos estruturais da condição de pagamento', function (): void {
    $condition = new PaymentCondition([
        'code' => '30-60',
        'name' => '30/60 dias',
        'installments' => 2,
        'first_due_days' => 30,
        'interval_days' => 30,
        'requires_down_payment' => false,
    ]);

    expect($condition->code)->toBe('30-60')
        ->and($condition->name)->toBe('30/60 dias')
        ->and($condition->installments)->toBe(2)
        ->and($condition->first_due_days)->toBe(30)
        ->and($condition->interval_days)->toBe(30)
        ->and($condition->requires_down_payment)->toBeFalse();
});
