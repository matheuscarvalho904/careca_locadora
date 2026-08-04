<?php

namespace App\Filament\Pages;

use App\Models\Quotation;
use App\Models\QuotationSupplier;
use App\Models\QuotationSupplierItem;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use UnitEnum;

class QuotationProposalEntry extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static ?string $navigationLabel = 'Lançar propostas';
    protected static ?string $title = 'Lançamento de propostas';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.quotation-proposal-entry';

    #[Url]
    public ?string $quotationId = null;

    #[Url]
    public ?string $supplierId = null;

    public ?string $proposalDate = null;
    public ?string $proposalValidUntil = null;
    public ?int $deliveryDays = null;
    public ?string $paymentConditionId = null;
    public float $freightValue = 0;
    public float $discountValue = 0;
    public float $additionalValue = 0;
    public ?string $notes = null;

    /** @var array<string, array<string, mixed>> */
    public array $items = [];

    public function mount(): void
    {
        $this->loadProposal();
    }

    public function updatedQuotationId(): void
    {
        $this->supplierId = null;
        $this->resetProposalFields();
    }

    public function updatedSupplierId(): void
    {
        $this->loadProposal();
    }

    public function getQuotationsProperty()
    {
        return Quotation::query()
            ->whereIn('status', ['draft', 'sent', 'collecting', 'analysis', 'selected'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getQuotationProperty(): ?Quotation
    {
        if (blank($this->quotationId)) {
            return null;
        }

        return Quotation::query()
            ->with(['items.product', 'suppliers.supplier'])
            ->find($this->quotationId);
    }

    public function getSuppliersProperty()
    {
        return $this->quotation?->suppliers ?? collect();
    }

    public function getPaymentConditionsProperty()
    {
        return \App\Models\PaymentCondition::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function loadProposal(): void
    {
        $this->resetProposalFields();

        if (blank($this->quotationId) || blank($this->supplierId)) {
            return;
        }

        $supplier = QuotationSupplier::query()
            ->with(['items.quotationItem'])
            ->where('quotation_id', $this->quotationId)
            ->find($this->supplierId);

        if (! $supplier) {
            return;
        }

        $this->proposalDate = $supplier->proposal_date?->toDateString();
        $this->proposalValidUntil = $supplier->proposal_valid_until?->toDateString();
        $this->deliveryDays = $supplier->delivery_days;
        $this->paymentConditionId = $supplier->payment_condition_id;
        $this->freightValue = (float) $supplier->freight_value;
        $this->discountValue = (float) $supplier->discount_value;
        $this->additionalValue = (float) $supplier->additional_value;
        $this->notes = $supplier->notes;

        foreach ($this->quotation?->items ?? [] as $quotationItem) {
            $proposal = $supplier->items
                ->firstWhere('quotation_item_id', $quotationItem->id);

            $this->items[$quotationItem->id] = [
                'unit_value' => (float) ($proposal?->unit_value ?? 0),
                'discount_value' => (float) ($proposal?->discount_value ?? 0),
                'delivery_days' => $proposal?->delivery_days,
                'notes' => $proposal?->notes,
            ];
        }
    }

    public function saveProposal(): void
    {
        if (blank($this->quotationId) || blank($this->supplierId)) {
            Notification::make()
                ->danger()
                ->title('Selecione a cotação e o fornecedor.')
                ->send();

            return;
        }

        $supplier = QuotationSupplier::query()
            ->where('quotation_id', $this->quotationId)
            ->findOrFail($this->supplierId);

        DB::transaction(function () use ($supplier): void {
            $supplier->update([
                'status' => 'responded',
                'proposal_date' => $this->proposalDate ?: today(),
                'proposal_valid_until' => $this->proposalValidUntil,
                'delivery_days' => $this->deliveryDays,
                'payment_condition_id' => $this->paymentConditionId,
                'freight_value' => $this->freightValue,
                'discount_value' => $this->discountValue,
                'additional_value' => $this->additionalValue,
                'notes' => $this->notes,
            ]);

            foreach ($this->quotation?->items ?? [] as $quotationItem) {
                $data = $this->items[$quotationItem->id] ?? [];

                QuotationSupplierItem::query()->updateOrCreate(
                    [
                        'quotation_supplier_id' => $supplier->id,
                        'quotation_item_id' => $quotationItem->id,
                    ],
                    [
                        'organization_id' => $supplier->organization_id,
                        'unit_value' => (float) ($data['unit_value'] ?? 0),
                        'discount_value' => (float) ($data['discount_value'] ?? 0),
                        'delivery_days' => filled($data['delivery_days'] ?? null)
                            ? (int) $data['delivery_days']
                            : null,
                        'notes' => $data['notes'] ?? null,
                    ],
                );
            }

            $supplier->refresh();
            $supplier->touch();

            $supplier->quotation?->update([
                'status' => 'analysis',
            ]);
        });

        $this->loadProposal();

        Notification::make()
            ->success()
            ->title('Proposta salva com sucesso.')
            ->send();
    }

    private function resetProposalFields(): void
    {
        $this->proposalDate = null;
        $this->proposalValidUntil = null;
        $this->deliveryDays = null;
        $this->paymentConditionId = null;
        $this->freightValue = 0;
        $this->discountValue = 0;
        $this->additionalValue = 0;
        $this->notes = null;
        $this->items = [];
    }
}
