<?php

namespace App\Filament\Pages;

use App\Models\Quotation;
use App\Models\QuotationSupplierItem;
use App\Services\Procurement\QuotationToPurchaseOrderService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Throwable;
use UnitEnum;

class QuotationComparison extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static ?string $navigationLabel = 'Mapa comparativo';
    protected static ?string $title = 'Mapa comparativo de cotações';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.quotation-comparison';

    #[Url]
    public ?string $quotationId = null;

    /** @var array<string, array<string, mixed>> */
    public array $meterReadings = [];

    public function mount(): void
    {
        $this->initializeMeterReadings();
    }

    public function hydrate(): void
    {
        $this->initializeMeterReadings();
    }

    public function updatedQuotationId(): void
    {
        $this->meterReadings = [];
        $this->initializeMeterReadings();
    }

    public function getQuotationProperty(): ?Quotation
    {
        if (blank($this->quotationId)) {
            return null;
        }

        return Quotation::query()
            ->with([
                'purchaseRequest',
                'items.product',
                'items.asset',
                'suppliers.supplier',
                'suppliers.paymentCondition',
                'suppliers.items.quotationItem.product',
                'suppliers.items.quotationItem.asset',
            ])
            ->find($this->quotationId);
    }

    public function getQuotationsProperty()
    {
        return Quotation::query()
            ->whereIn('status', ['collecting', 'analysis', 'selected'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getSelectedAssetItemsProperty(): Collection
    {
        if (! $this->quotation) {
            return collect();
        }

        $selectedIds = $this->quotation->suppliers
            ->flatMap(fn ($supplier) => $supplier->items)
            ->where('is_selected', true)
            ->pluck('quotation_item_id')
            ->unique()
            ->values();

        return $this->quotation->items
            ->where('application_type', 'asset')
            ->whereIn('id', $selectedIds)
            ->values();
    }

    public function selectWinner(string $quotationItemId, string $proposalItemId): void
    {
        DB::transaction(function () use ($quotationItemId, $proposalItemId): void {
            QuotationSupplierItem::query()
                ->whereHas(
                    'quotationSupplier',
                    fn ($query) => $query->where('quotation_id', $this->quotationId)
                )
                ->where('quotation_item_id', $quotationItemId)
                ->update([
                    'is_selected' => false,
                ]);

            $selected = QuotationSupplierItem::query()
                ->where('quotation_item_id', $quotationItemId)
                ->findOrFail($proposalItemId);

            $selected->update([
                'is_selected' => true,
            ]);

            $selected->quotationSupplier->update([
                'status' => 'selected',
            ]);

            $selected->quotationSupplier->quotation->update([
                'status' => 'selected',
            ]);
        });

        unset($this->meterReadings[$quotationItemId]);
        $this->initializeMeterReadings();

        Notification::make()
            ->success()
            ->title('Fornecedor selecionado para o item.')
            ->send();
    }

    public function generateOrders(): void
    {
        if (! $this->quotation) {
            return;
        }

        $this->initializeMeterReadings();
        $this->normalizeMeterReadings();

        try {
            $orders = app(QuotationToPurchaseOrderService::class)
                ->convert($this->quotation, $this->meterReadings);

            Notification::make()
                ->success()
                ->title('Ordens de Compra geradas')
                ->body("Foram geradas {$orders->count()} Ordem(ns) de Compra.")
                ->send();

            $this->redirect(
                \App\Filament\Resources\PurchaseOrders\PurchaseOrderResource::getUrl('index')
            );
        } catch (Throwable $exception) {
            Notification::make()
                ->danger()
                ->title('Não foi possível gerar as OCs')
                ->body($exception->getMessage())
                ->send();
        }
    }

    private function initializeMeterReadings(): void
    {
        if (blank($this->quotationId)) {
            return;
        }

        foreach ($this->selectedAssetItems as $item) {
            $key = (string) $item->id;
            $current = $this->meterReadings[$key] ?? [];

            $this->meterReadings[$key] = [
                'meter_type' => $current['meter_type']
                    ?? ($item->asset?->measurement_type === 'hourmeter'
                        ? 'hourmeter'
                        : 'odometer'),
                'meter_reading' => $current['meter_reading'] ?? '',
                'meter_recorded_at' => $current['meter_recorded_at']
                    ?? now()->format('Y-m-d\TH:i'),
            ];
        }

        $validKeys = $this->selectedAssetItems
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        $this->meterReadings = collect($this->meterReadings)
            ->only($validKeys)
            ->all();
    }

    private function normalizeMeterReadings(): void
    {
        foreach ($this->meterReadings as $itemId => $reading) {
            $rawValue = $reading['meter_reading'] ?? null;

            $this->meterReadings[$itemId]['meter_type'] = filled($reading['meter_type'] ?? null)
                ? (string) $reading['meter_type']
                : null;

            $this->meterReadings[$itemId]['meter_reading'] = is_string($rawValue)
                ? str_replace(',', '.', trim($rawValue))
                : $rawValue;

            $this->meterReadings[$itemId]['meter_recorded_at'] =
                $reading['meter_recorded_at'] ?? now()->format('Y-m-d\TH:i');
        }
    }
}
