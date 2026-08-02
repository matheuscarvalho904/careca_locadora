<?php

namespace App\Services\Finance;

use App\Models\AccountReceivable;
use App\Models\CashMovement;
use App\Models\FinancialReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReceiptService
{
    public function register(
        AccountReceivable $receivable,
        array $data,
    ): FinancialReceipt {
        return DB::transaction(function () use ($receivable, $data): FinancialReceipt {
            $receivable->refresh();

            if ($receivable->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Uma conta cancelada não pode receber pagamentos.',
                ]);
            }

            if ((float) $receivable->open_value <= 0) {
                throw ValidationException::withMessages([
                    'open_value' => 'Esta conta não possui saldo em aberto.',
                ]);
            }

            $principal = (float) ($data['principal_value'] ?? 0);
            $interest = (float) ($data['interest_value'] ?? 0);
            $penalty = (float) ($data['penalty_value'] ?? 0);
            $discount = (float) ($data['discount_value'] ?? 0);
            $additional = (float) ($data['additional_value'] ?? 0);

            if ($principal <= 0) {
                throw ValidationException::withMessages([
                    'principal_value' => 'Informe um valor principal maior que zero.',
                ]);
            }

            if ($principal > (float) $receivable->open_value) {
                throw ValidationException::withMessages([
                    'principal_value' => 'O valor principal não pode ultrapassar o saldo em aberto.',
                ]);
            }

            $receipt = FinancialReceipt::query()->create([
                'organization_id' => $receivable->organization_id,
                'account_receivable_id' => $receivable->id,
                'rental_invoice_id' => $receivable->rental_invoice_id,
                'business_partner_id' => $receivable->business_partner_id,
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'received_at' => $data['received_at'] ?? now(),
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'principal_value' => $principal,
                'interest_value' => $interest,
                'penalty_value' => $penalty,
                'discount_value' => $discount,
                'additional_value' => $additional,
                'proof_path' => $data['proof_path'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            CashMovement::query()->create([
                'organization_id' => $receivable->organization_id,
                'financial_account_id' => $receipt->financial_account_id,
                'financial_receipt_id' => $receipt->id,
                'created_by' => auth()->id(),
                'type' => 'entry',
                'status' => 'posted',
                'occurred_at' => $receipt->received_at,
                'value' => $receipt->total_received,
                'description' => "Recebimento {$receipt->number} - {$receivable->number}",
                'notes' => $receipt->notes,
            ]);

            $this->synchronizeReceivable($receivable);

            return $receipt->fresh([
                'receivable',
                'invoice',
                'customer',
                'financialAccount',
            ]);
        });
    }

    public function reverse(
        FinancialReceipt $receipt,
        string $reason,
    ): FinancialReceipt {
        return DB::transaction(function () use ($receipt, $reason): FinancialReceipt {
            $receipt->refresh();

            if ($receipt->status === 'reversed') {
                throw ValidationException::withMessages([
                    'status' => 'Este recebimento já foi estornado.',
                ]);
            }

            $receipt->update([
                'status' => 'reversed',
                'reversed_at' => now(),
                'reversed_by' => auth()->id(),
                'reversal_reason' => $reason,
            ]);

            CashMovement::query()->create([
                'organization_id' => $receipt->organization_id,
                'financial_account_id' => $receipt->financial_account_id,
                'financial_receipt_id' => $receipt->id,
                'created_by' => auth()->id(),
                'type' => 'exit',
                'status' => 'posted',
                'occurred_at' => now(),
                'value' => $receipt->total_received,
                'description' => "Estorno do recebimento {$receipt->number}",
                'notes' => $reason,
            ]);

            $this->synchronizeReceivable($receipt->receivable);

            return $receipt->fresh();
        });
    }

    public function synchronizeReceivable(AccountReceivable $receivable): void
    {
        $confirmed = $receivable->receipts()
            ->where('status', 'confirmed');

        $principal = (float) (clone $confirmed)->sum('principal_value');
        $interest = (float) (clone $confirmed)->sum('interest_value');
        $penalty = (float) (clone $confirmed)->sum('penalty_value');
        $discount = (float) (clone $confirmed)->sum('discount_value');
        $additional = (float) (clone $confirmed)->sum('additional_value');

        $receivable->forceFill([
            'paid_value' => $principal,
            'interest_value' => $interest + $additional,
            'penalty_value' => $penalty,
            'discount_value' => $discount,
            'paid_at' => null,
        ])->save();

        if ($receivable->status === 'paid') {
            $receivable->updateQuietly([
                'paid_at' => $receivable->receipts()
                    ->where('status', 'confirmed')
                    ->latest('received_at')
                    ->value('received_at') ?? now(),
            ]);
        }

        $invoice = $receivable->invoice;

        if ($invoice !== null) {
            $invoice->update([
                'received_value' => (float) $invoice->receivables()
                    ->sum('paid_value'),
            ]);
        }
    }
}
