<?php

use App\Models\AccountReceivable;
use App\Models\BusinessPartner;
use App\Models\FinancialAccount;
use App\Models\Organization;
use App\Models\RentalInvoice;
use App\Services\Finance\ReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registra recebimento parcial e total e permite estorno', function (): void {
    $organization = Organization::factory()->create();

    $customer = BusinessPartner::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'roles' => ['customer'],
            'person_type' => 'legal',
            'legal_name' => 'Cliente Financeiro',
            'status' => 'active',
        ]);

    $invoice = RentalInvoice::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'contract_id' => \App\Models\RentalContract::query()
                ->withoutOrganizationScope()
                ->create([
                    'organization_id' => $organization->id,
                    'business_partner_id' => $customer->id,
                    'status' => 'closed',
                    'starts_at' => now()->subDay(),
                    'ends_at' => now(),
                    'subtotal' => 1000,
                    'total_value' => 1000,
                ])->id,
            'business_partner_id' => $customer->id,
            'status' => 'issued',
            'issued_at' => today(),
            'due_at' => today()->addDays(10),
            'competence_date' => today()->startOfMonth(),
            'subtotal' => 1000,
            'total_value' => 1000,
            'open_value' => 1000,
        ]);

    $receivable = AccountReceivable::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'rental_invoice_id' => $invoice->id,
            'business_partner_id' => $customer->id,
            'issued_at' => today(),
            'due_at' => today()->addDays(10),
            'original_value' => 1000,
            'open_value' => 1000,
        ]);

    $account = FinancialAccount::query()
        ->withoutOrganizationScope()
        ->create([
            'organization_id' => $organization->id,
            'name' => 'Banco Teste',
            'type' => 'bank',
            'status' => 'active',
        ]);

    $service = app(ReceiptService::class);

    $first = $service->register($receivable, [
        'principal_value' => 400,
        'payment_method' => 'pix',
        'financial_account_id' => $account->id,
        'received_at' => now(),
    ]);

    expect($receivable->fresh()->status)->toBe('partially_paid')
        ->and((float) $receivable->fresh()->open_value)->toBe(600.0);

    $service->register($receivable->fresh(), [
        'principal_value' => 600,
        'payment_method' => 'transfer',
        'financial_account_id' => $account->id,
        'received_at' => now(),
    ]);

    expect($receivable->fresh()->status)->toBe('paid')
        ->and((float) $receivable->fresh()->open_value)->toBe(0.0)
        ->and($invoice->fresh()->status)->toBe('paid');

    $service->reverse($first, 'Teste de estorno');

    expect($first->fresh()->status)->toBe('reversed')
        ->and($receivable->fresh()->status)->toBe('partially_paid')
        ->and((float) $receivable->fresh()->open_value)->toBe(400.0);
});
