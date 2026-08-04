<?php

namespace App\Services\Documents;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Model;

final class ProcurementDocumentService
{
    public function purchaseOrderData(PurchaseOrder $order): array
    {
        $order->loadMissing([
            'supplier.addresses',
            'supplier.contacts',
            'paymentCondition',
            'items.product.unit',
            'items.applicationCenter',
            'items.asset.category',
            'items.asset.company',
            'items.asset.branch',
            'items.warehouse.company',
            'items.warehouse.branch',
            'items.costCenter.company',
            'items.costCenter.branch',
        ]);

        return $this->build(
            type: 'purchase_order',
            order: $order,
            title: 'ORDEM DE COMPRA',
            expectedDate: $order->expected_delivery_at,
        );
    }

    public function serviceOrderData(ServiceOrder $order): array
    {
        $order->loadMissing([
            'supplier.addresses',
            'supplier.contacts',
            'paymentCondition',
            'items.unit',
            'items.applicationCenter',
            'items.asset.category',
            'items.asset.company',
            'items.asset.branch',
            'items.costCenter.company',
            'items.costCenter.branch',
        ]);

        return $this->build(
            type: 'service_order',
            order: $order,
            title: 'ORDEM DE SERVIÇO',
            expectedDate: $order->expected_execution_at,
        );
    }

    private function build(
        string $type,
        PurchaseOrder|ServiceOrder $order,
        string $title,
        mixed $expectedDate,
    ): array {
        [$company, $branch] = $this->resolveCompanyAndBranch($order);

        $payload = [
            'type' => $type,
            'id' => $order->id,
            'number' => $order->number,
            'status' => $order->status,
            'updated_at' => $order->updated_at?->toIso8601String(),
            'total' => (string) $order->total_value,
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'total' => (string) $item->total_value,
            ])->all(),
        ];

        return [
            'documentType' => $type,
            'documentTitle' => $title,
            'order' => $order,
            'company' => $company,
            'branch' => $branch,
            'supplier' => $order->supplier,
            'supplierAddress' => $this->primaryRelated($order->supplier, 'addresses'),
            'supplierContact' => $this->primaryRelated($order->supplier, 'contacts'),
            'statusLabel' => $this->statusLabel($order->status),
            'statusClass' => $this->statusClass($order->status),
            'paymentMethodLabel' => $this->paymentMethodLabel(
                $order->payment_method
            ),
            'originLabel' => $this->originLabel($order->origin_type),
            'expectedDate' => $expectedDate,
            'installments' => $this->installments($order),
            'documentHash' => hash(
                'sha256',
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            ),
            'generatedAt' => now(),
            'logo' => $this->logoDataUri(),
        ];
    }

    private function resolveCompanyAndBranch(
        PurchaseOrder|ServiceOrder $order,
    ): array {
        $company = null;
        $branch = null;

        foreach ($order->items as $item) {
            $company ??= $item->asset?->company
                ?? $item->warehouse?->company
                ?? $item->costCenter?->company;

            $branch ??= $item->asset?->branch
                ?? $item->warehouse?->branch
                ?? $item->costCenter?->branch;

            if ($company && $branch) {
                break;
            }
        }

        $user = auth()->user();

        $company ??= $user?->defaultCompany;
        $branch ??= $user?->defaultBranch;

        $company ??= Company::query()
            ->where('organization_id', $order->organization_id)
            ->orderBy('created_at')
            ->first();

        $branch ??= Branch::query()
            ->where('organization_id', $order->organization_id)
            ->when(
                $company,
                fn ($query) => $query->where('company_id', $company->id)
            )
            ->orderBy('created_at')
            ->first();

        return [$company, $branch];
    }

    private function primaryRelated(?Model $model, string $relation): ?Model
    {
        if (! $model || ! $model->relationLoaded($relation)) {
            return null;
        }

        $records = $model->getRelation($relation);

        return $records?->firstWhere('is_primary', true)
            ?? $records?->first();
    }

    private function installments(
        PurchaseOrder|ServiceOrder $order,
    ): array {
        $count = max(1, (int) ($order->installments ?? 1));
        $interval = max(0, (int) ($order->installment_interval_days ?? 0));
        $first = $order->first_due_date;
        $total = (float) $order->total_value;

        if (! $first) {
            return [];
        }

        $base = round($total / $count, 2);
        $remaining = round($total - ($base * $count), 2);
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $amount = $base;

            if ($index === $count - 1) {
                $amount += $remaining;
            }

            $rows[] = [
                'number' => $index + 1,
                'due_date' => $first->copy()->addDays($interval * $index),
                'amount' => $amount,
            ];
        }

        return $rows;
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'RASCUNHO',
            'awaiting_approval' => 'AGUARDANDO APROVAÇÃO',
            'approved' => 'APROVADA',
            'sent' => 'ENVIADA',
            'partially_received' => 'RECEBIDA PARCIALMENTE',
            'received' => 'RECEBIDA',
            'finished' => 'FINALIZADA',
            'in_execution' => 'EM EXECUÇÃO',
            'completed' => 'CONCLUÍDA',
            'cancelled' => 'CANCELADA',
            default => mb_strtoupper((string) $status),
        };
    }

    private function statusClass(?string $status): string
    {
        return match ($status) {
            'approved', 'received', 'finished', 'completed' => 'status-green',
            'sent', 'partially_received', 'in_execution' => 'status-blue',
            'awaiting_approval' => 'status-amber',
            'cancelled' => 'status-red',
            default => 'status-gray',
        };
    }

    private function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'pix' => 'PIX',
            'bank_slip' => 'Boleto',
            'bank_transfer' => 'Transferência bancária',
            'cash' => 'Dinheiro',
            'credit_card' => 'Cartão de crédito',
            'debit_card' => 'Cartão de débito',
            'check' => 'Cheque',
            'other' => 'Outra',
            default => 'Não informada',
        };
    }

    private function originLabel(?string $origin): string
    {
        return match ($origin) {
            'request_quotation' => 'Solicitação/Cotação',
            'request_direct' => 'Solicitação direta',
            'direct' => 'Direta',
            'emergency' => 'Emergencial',
            'contract' => 'Contrato vigente',
            'regularization' => 'Regularização',
            default => 'Não informada',
        };
    }

    private function logoDataUri(): ?string
    {
        foreach ([
            public_path('images/careca-locadora-logo.png'),
            public_path('images/logo.png'),
        ] as $path) {
            if (! is_file($path)) {
                continue;
            }

            return 'data:image/png;base64,' . base64_encode(
                file_get_contents($path)
            );
        }

        return null;
    }
}
