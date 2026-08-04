<?php

namespace App\Services\Finance;

use App\Models\AccountPayable;
use App\Models\CashMovement;
use App\Models\FinancialPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PaymentService
{
    public function approve(AccountPayable $payable): AccountPayable
    {
        if (! in_array(
            $payable->status,
            ['draft', 'awaiting_approval'],
            true
        )) {
            throw ValidationException::withMessages([
                'status' => 'Esta conta não pode ser aprovada no status atual.',
            ]);
        }

        $payable->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return $payable->fresh();
    }

    public function register(
        AccountPayable $payable,
        array $data,
    ): FinancialPayment {
        return DB::transaction(function () use (
            $payable,
            $data,
        ): FinancialPayment {
            $payable = AccountPayable::query()
                ->with('payments')
                ->lockForUpdate()
                ->findOrFail($payable->id);

            if (! in_array(
                $payable->status,
                ['approved', 'overdue', 'partially_paid'],
                true
            )) {
                throw ValidationException::withMessages([
                    'status' => 'A conta precisa estar aprovada para pagamento.',
                ]);
            }

            $principal = (float) ($data['principal_value'] ?? 0);

            if (
                $principal <= 0
                || $principal > (float) $payable->open_value
            ) {
                throw ValidationException::withMessages([
                    'principal_value' => 'Valor principal inválido.',
                ]);
            }

            $payment = FinancialPayment::query()->create([
                'organization_id' => $payable->organization_id,
                'account_payable_id' => $payable->id,
                'supplier_id' => $payable->supplier_id,
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'principal_value' => $principal,
                'interest_value' => (float) ($data['interest_value'] ?? 0),
                'penalty_value' => (float) ($data['penalty_value'] ?? 0),
                'discount_value' => (float) ($data['discount_value'] ?? 0),
                'additional_value' => (float) ($data['additional_value'] ?? 0),
                'proof_path' => $data['proof_path'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->postPaymentCashMovement($payment, $payable);
            $this->synchronize($payable);

            return $payment->fresh();
        });
    }

    public function reverse(
        FinancialPayment $payment,
        string $reason,
    ): FinancialPayment {
        return DB::transaction(function () use (
            $payment,
            $reason,
        ): FinancialPayment {
            $payment = FinancialPayment::query()
                ->with('payable')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->status === 'reversed') {
                throw ValidationException::withMessages([
                    'status' => 'Este pagamento já foi estornado.',
                ]);
            }

            $payment->update([
                'status' => 'reversed',
                'reversed_at' => now(),
                'reversed_by' => auth()->id(),
                'reversal_reason' => $reason,
            ]);

            $this->postReversalCashMovement($payment, $reason);
            $this->synchronize($payment->payable);

            return $payment->fresh();
        });
    }

    public function postPaymentCashMovement(
        FinancialPayment $payment,
        AccountPayable $payable,
    ): CashMovement {
        return CashMovement::query()->firstOrCreate(
            [
                'organization_id' => $payment->organization_id,
                'source_type' => FinancialPayment::class,
                'source_id' => $payment->id,
                'category' => 'payment',
            ],
            [
                'financial_account_id' => $payment->financial_account_id,
                'created_by' => auth()->id(),
                'type' => 'exit',
                'status' => 'posted',
                'reconciliation_status' => 'pending',
                'occurred_at' => $payment->paid_at,
                'value' => $payment->total_paid,
                'description' => "Pagamento {$payment->number} - {$payable->number}",
                'notes' => $payment->notes,
                'metadata' => [
                    'financial_payment_number' => $payment->number,
                    'account_payable_number' => $payable->number,
                    'payment_method' => $payment->payment_method,
                ],
            ]
        );
    }

    public function postReversalCashMovement(
        FinancialPayment $payment,
        string $reason,
    ): CashMovement {
        return CashMovement::query()->firstOrCreate(
            [
                'organization_id' => $payment->organization_id,
                'source_type' => FinancialPayment::class,
                'source_id' => $payment->id,
                'category' => 'payment_reversal',
            ],
            [
                'financial_account_id' => $payment->financial_account_id,
                'created_by' => auth()->id(),
                'type' => 'entry',
                'status' => 'posted',
                'reconciliation_status' => 'pending',
                'occurred_at' => now(),
                'value' => $payment->total_paid,
                'description' => "Estorno do pagamento {$payment->number}",
                'notes' => $reason,
                'metadata' => [
                    'financial_payment_number' => $payment->number,
                    'reversal_reason' => $reason,
                ],
            ]
        );
    }

    public function synchronize(AccountPayable $payable): void
    {
        $confirmed = $payable->payments()
            ->where('status', 'confirmed');

        $payable->forceFill([
            'paid_value' => (float) (clone $confirmed)
                ->sum('principal_value'),

            'interest_value' => (float) (clone $confirmed)
                ->sum('interest_value')
                + (float) (clone $confirmed)
                    ->sum('additional_value'),

            'penalty_value' => (float) (clone $confirmed)
                ->sum('penalty_value'),

            'discount_value' => (float) (clone $confirmed)
                ->sum('discount_value'),
        ])->save();
    }
}
