<?php

namespace App\Services\Procurement;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class QuotationToPurchaseOrderService
{
    /**
     * @param array<string, array<string, mixed>> $meterReadings
     * @return Collection<int, PurchaseOrder>
     */
    public function convert(Quotation $quotation, array $meterReadings = []): Collection
    {
        return DB::transaction(function () use ($quotation, $meterReadings): Collection {
            $quotation->load([
                'purchaseRequest',
                'suppliers.supplier',
                'suppliers.paymentCondition',
                'suppliers.items.quotationItem.asset',
            ]);

            $selected = $quotation->suppliers
                ->flatMap(fn ($supplier) => $supplier->items)
                ->where('is_selected', true);

            if ($selected->isEmpty()) {
                throw ValidationException::withMessages([
                    'quotation' => 'Selecione ao menos uma proposta antes de gerar a Ordem de Compra.',
                ]);
            }

            foreach ($selected as $selectedItem) {
                $quotationItem = $selectedItem->quotationItem;

                if ($quotationItem?->application_type !== 'asset') {
                    continue;
                }

                $key = (string) $quotationItem->id;
                $reading = $meterReadings[$key] ?? [];

                if (
                    blank($reading['meter_type'] ?? null)
                    || ! array_key_exists('meter_reading', $reading)
                    || blank((string) ($reading['meter_reading'] ?? ''))
                ) {
                    $asset = $quotationItem->asset?->prefix
                        ?: $quotationItem->asset?->name
                        ?: 'ativo não identificado';

                    throw ValidationException::withMessages([
                        'meter_reading' => "Informe hodômetro ou horímetro para o item aplicado no ativo {$asset}.",
                    ]);
                }
            }

            $orders = collect();

            foreach ($selected->groupBy('quotation_supplier_id') as $supplierId => $items) {
                $supplier = $quotation->suppliers->firstWhere('id', $supplierId);

                if (! $supplier) {
                    continue;
                }

                $order = PurchaseOrder::query()->create([
                    'organization_id' => $quotation->organization_id,
                    'supplier_id' => $supplier->supplier_id,
                    'purchase_request_id' => $quotation->purchase_request_id,
                    'quotation_id' => $quotation->id,
                    'origin_type' => 'request_quotation',
                    'status' => 'draft',
                    'issued_at' => today(),
                    'expected_delivery_at' => filled($supplier->delivery_days)
                        ? today()->addDays($supplier->delivery_days)
                        : null,
                    'payment_condition_id' => $supplier->payment_condition_id,
                    'installments' => $supplier->paymentCondition?->installments ?? 1,
                    'installment_interval_days' => $supplier->paymentCondition?->interval_days ?? 30,
                    'first_due_date' => filled($supplier->paymentCondition)
                        ? today()->addDays($supplier->paymentCondition->first_due_days)
                        : null,
                    'freight_value' => $supplier->freight_value,
                    'discount_value' => $supplier->discount_value,
                    'additional_value' => $supplier->additional_value,
                    'supplier_notes' => $supplier->notes,
                    'internal_notes' => "Gerada pela cotação {$quotation->number}.",
                ]);

                foreach ($items as $selectedItem) {
                    $quotationItem = $selectedItem->quotationItem;
                    $key = (string) $quotationItem->id;
                    $reading = $meterReadings[$key] ?? [];

                    $order->items()->create([
                        'organization_id' => $quotation->organization_id,
                        'product_id' => $quotationItem->product_id,
                        'application_center_id' => $quotationItem->application_center_id,
                        'asset_id' => $quotationItem->asset_id,
                        'warehouse_id' => $quotationItem->warehouse_id,
                        'cost_center_id' => $quotationItem->cost_center_id,
                        'application_type' => $quotationItem->application_type,
                        'quantity' => $quotationItem->quantity,
                        'unit_value' => $selectedItem->unit_value,
                        'discount_value' => $selectedItem->discount_value,
                        'meter_type' => $quotationItem->application_type === 'asset'
                            ? ($reading['meter_type'] ?? null)
                            : null,
                        'meter_reading' => $quotationItem->application_type === 'asset'
                            ? ($reading['meter_reading'] ?? null)
                            : null,
                        'meter_recorded_at' => $quotationItem->application_type === 'asset'
                            ? ($reading['meter_recorded_at'] ?? now())
                            : null,
                        'notes' => $quotationItem->notes,
                    ]);
                }

                $order = app(PurchaseOrderTotalsService::class)
                    ->recalculate($order);

                $orders->push($order);
            }

            $quotation->update([
                'status' => 'converted',
                'closed_at' => now(),
            ]);

            $quotation->purchaseRequest?->update([
                'status' => 'converted',
            ]);

            return $orders;
        });
    }
}
