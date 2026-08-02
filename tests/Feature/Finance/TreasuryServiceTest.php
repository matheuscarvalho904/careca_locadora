<?php

use App\Models\FinancialAccount;
use App\Models\Organization;
use App\Services\Finance\TreasuryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registra suprimento sangria transferência e conciliação', function (): void {
    $organization = Organization::factory()->create();

    $cash = FinancialAccount::query()->withoutOrganizationScope()->create([
        'organization_id'=>$organization->id,'name'=>'Caixa','type'=>'cash',
        'opening_balance'=>1000,'status'=>'active',
    ]);

    $bank = FinancialAccount::query()->withoutOrganizationScope()->create([
        'organization_id'=>$organization->id,'name'=>'Banco','type'=>'bank',
        'opening_balance'=>0,'status'=>'active',
    ]);

    $service = app(TreasuryService::class);
    $supply = $service->supply($cash,500,'Troco inicial');
    $service->withdrawal($cash,200,'Sangria');

    expect($cash->fresh()->current_balance)->toBe(1300.0);

    $transfer = $service->transfer($cash->fresh(),$bank,300);

    expect($cash->fresh()->current_balance)->toBe(1000.0)
        ->and($bank->fresh()->current_balance)->toBe(300.0)
        ->and($transfer['exit']->transfer_group_id)->toBe($transfer['entry']->transfer_group_id);

    $service->reconcile($supply);
    expect($supply->fresh()->reconciliation_status)->toBe('reconciled');
});
