<?php

namespace App\Services\Rentals;

use App\Models\RentalContract;
use App\Models\RentalInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RentalInvoiceService
{
    public function createFromContract(RentalContract $contract): RentalInvoice
    {
        return DB::transaction(function () use ($contract): RentalInvoice {
            $contract->loadMissing([
                'items.asset',
                'rentalReturn.items.asset',
                'customer',
            ]);

            if ($contract->status !== 'closed') {
                throw ValidationException::withMessages([
                    'status' => 'A fatura de locação só pode ser gerada para contrato encerrado.',
                ]);
            }

            $existing = RentalInvoice::query()
                ->where('contract_id', $contract->id)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $invoice = RentalInvoice::query()->create([
                'organization_id' => $contract->organization_id,
                'contract_id' => $contract->id,
                'return_id' => $contract->rentalReturn?->id,
                'business_partner_id' => $contract->business_partner_id,
                'company_id' => $contract->company_id,
                'branch_id' => $contract->branch_id,
                'cost_center_id' => $contract->cost_center_id,
                'status' => 'draft',
                'issued_at' => null,
                'due_at' => now()->addDays(10)->toDateString(),
                'competence_date' => now()->startOfMonth()->toDateString(),
                'discount_value' => $contract->discount_value,
                'additional_value' => 0,
                'notes' => "Fatura de locação referente ao contrato {$contract->number}.",
            ]);

            foreach ($contract->items as $item) {
                $invoice->items()->create([
                    'organization_id' => $contract->organization_id,
                    'asset_id' => $item->asset_id,
                    'type' => 'rental',
                    'description' => $this->rentalDescription($item),
                    'quantity' => $item->quantity,
                    'unit_value' => $item->unit_value,
                    'discount_value' => $item->discount_value,
                    'additional_value' => $item->additional_value,
                ]);
            }

            $return = $contract->rentalReturn;

            if ($return !== null) {
                $this->addAdditionalCharge($invoice, 'extra_time', 'Tempo excedente', $return->extra_time_value);
                $this->addAdditionalCharge($invoice, 'mileage', 'Quilometragem excedente', $return->mileage_value);
                $this->addAdditionalCharge($invoice, 'fuel', 'Reposição de combustível', $return->fuel_value);
                $this->addAdditionalCharge($invoice, 'damage', 'Avarias identificadas na devolução', $return->damage_value);
                $this->addAdditionalCharge($invoice, 'cleaning', 'Lavagem e limpeza', $return->cleaning_value);
                $this->addAdditionalCharge($invoice, 'missing_accessories', 'Acessórios ou documentos faltantes', $return->missing_accessories_value);
                $this->addAdditionalCharge($invoice, 'other', 'Outras cobranças da devolução', $return->other_value);
            }

            return $this->recalculate($invoice);
        });
    }

    public function recalculate(RentalInvoice $invoice): RentalInvoice
    {
        return DB::transaction(function () use ($invoice): RentalInvoice {
            $subtotal = (float) $invoice->items()->sum('total_value');

            $invoice->forceFill([
                'subtotal' => $subtotal,
            ])->save();

            return $invoice->fresh(['items.asset', 'contract', 'customer']);
        });
    }

    public function issue(
        RentalInvoice $invoice,
        int $installments = 1,
    ): RentalInvoice {
        return DB::transaction(function () use ($invoice, $installments): RentalInvoice {
            if ($invoice->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Uma fatura cancelada não pode ser emitida.',
                ]);
            }

            if ($invoice->items()->doesntExist()) {
                throw ValidationException::withMessages([
                    'items' => 'A fatura precisa possuir ao menos um item.',
                ]);
            }

            $installments = max(1, min(120, $installments));

            $invoice = $this->recalculate($invoice);

            $invoice->update([
                'issued_at' => $invoice->issued_at ?? now()->toDateString(),
                'status' => 'issued',
            ]);

            if ($invoice->receivables()->doesntExist()) {
                $this->generateReceivables($invoice, $installments);
            }

            return $invoice->fresh(['items', 'receivables', 'customer']);
        });
    }

    private function generateReceivables(
        RentalInvoice $invoice,
        int $installments,
    ): void {
        $totalInCents = (int) round((float) $invoice->total_value * 100);
        $base = intdiv($totalInCents, $installments);
        $remainder = $totalInCents % $installments;

        for ($number = 1; $number <= $installments; $number++) {
            $valueInCents = $base + ($number <= $remainder ? 1 : 0);
            $value = $valueInCents / 100;

            $invoice->receivables()->create([
                'organization_id' => $invoice->organization_id,
                'business_partner_id' => $invoice->business_partner_id,
                'installment_number' => $number,
                'installments_count' => $installments,
                'status' => 'open',
                'issued_at' => $invoice->issued_at,
                'due_at' => $invoice->due_at->copy()->addMonthsNoOverflow($number - 1),
                'original_value' => $value,
                'open_value' => $value,
                'notes' => "Parcela {$number}/{$installments} da fatura {$invoice->number}.",
            ]);
        }
    }

    private function addAdditionalCharge(
        RentalInvoice $invoice,
        string $type,
        string $description,
        mixed $value,
    ): void {
        $value = (float) ($value ?? 0);

        if ($value <= 0) {
            return;
        }

        $invoice->items()->create([
            'organization_id' => $invoice->organization_id,
            'type' => $type,
            'description' => $description,
            'quantity' => 1,
            'unit_value' => $value,
        ]);
    }

    private function rentalDescription(mixed $item): string
    {
        $asset = trim(
            ($item->asset?->prefix ?? '')
            . ' — '
            . ($item->asset?->name ?? 'Ativo')
        );

        return "Locação de {$asset}";
    }
}
