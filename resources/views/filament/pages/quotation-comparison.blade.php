<x-filament-panels::page>
    <style>
        .careca-select {
            width: 100%;
            min-height: 44px;
            border: 1px solid rgb(63 63 70);
            border-radius: 0.75rem;
            background: rgb(24 24 27);
            color: rgb(244 244 245);
            padding: 0.65rem 2.5rem 0.65rem 0.85rem;
            font-size: 0.95rem;
            outline: none;
        }

        .careca-select:focus {
            border-color: rgb(245 158 11);
            box-shadow: 0 0 0 1px rgb(245 158 11);
        }

        .careca-select option {
            background: #ffffff;
            color: #111827;
        }

        .comparison-wrap {
            overflow-x: auto;
            border: 1px solid rgb(63 63 70);
            border-radius: 1rem;
            background: rgba(9, 9, 11, .45);
        }

        .comparison-table {
            width: 100%;
            min-width: 1050px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .comparison-table th {
            padding: 1rem;
            border-bottom: 1px solid rgb(63 63 70);
            background: rgb(39 39 42);
            color: rgb(250 250 250);
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .025em;
            text-transform: uppercase;
            vertical-align: bottom;
        }

        .comparison-table td {
            padding: 1rem;
            border-bottom: 1px solid rgb(39 39 42);
            vertical-align: top;
        }

        .comparison-table tbody tr:hover td {
            background: rgba(39, 39, 42, .35);
        }

        .supplier-card {
            min-width: 220px;
            text-align: center;
        }

        .supplier-name {
            display: block;
            font-size: .9rem;
            font-weight: 800;
            color: white;
            line-height: 1.3;
        }

        .supplier-meta {
            display: block;
            margin-top: .3rem;
            color: rgb(161 161 170);
            font-size: .75rem;
            font-weight: 500;
            text-transform: none;
        }

        .proposal-card {
            min-height: 150px;
            border: 1px solid rgb(63 63 70);
            border-radius: .9rem;
            background: rgba(24, 24, 27, .72);
            padding: 1rem;
            text-align: center;
        }

        .proposal-card.best {
            border-color: rgb(34 197 94);
            background: rgba(20, 83, 45, .18);
        }

        .proposal-total {
            font-size: 1.12rem;
            font-weight: 800;
            color: white;
        }

        .proposal-card.best .proposal-total {
            color: rgb(74 222 128);
        }

        .best-badge {
            display: inline-flex;
            margin-top: .55rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, .18);
            padding: .25rem .65rem;
            color: rgb(74 222 128);
            font-size: .72rem;
            font-weight: 700;
        }

        .no-proposal {
            display: flex;
            min-height: 150px;
            align-items: center;
            justify-content: center;
            border: 1px dashed rgb(82 82 91);
            border-radius: .9rem;
            padding: 1rem;
            color: rgb(161 161 170);
            text-align: center;
        }
    </style>

    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Selecionar cotação</x-slot>
            <x-slot name="description">
                Selecione uma cotação em análise para comparar preços, prazos e condições.
            </x-slot>

            <select wire:model.live="quotationId" class="careca-select max-w-3xl">
                <option value="">Selecione uma cotação...</option>
                @foreach ($this->quotations as $quotation)
                    <option value="{{ $quotation->id }}">
                        {{ $quotation->number }}
                        @if ($quotation->purchaseRequest)
                            — {{ $quotation->purchaseRequest->number }}
                        @endif
                    </option>
                @endforeach
            </select>
        </x-filament::section>

        @if ($this->quotation)
            <x-filament::section>
                <x-slot name="heading">
                    Comparativo — {{ $this->quotation->number }}
                </x-slot>

                <x-slot name="description">
                    Compare propostas e selecione o fornecedor vencedor para cada produto.
                </x-slot>

                <div class="mb-5 flex flex-wrap gap-2">
                    @foreach ($this->quotation->suppliers as $supplier)
                        <x-filament::button
                            tag="a"
                            size="sm"
                            color="gray"
                            icon="heroicon-o-pencil-square"
                            :href="\App\Filament\Pages\QuotationProposalEntry::getUrl([
                                'quotationId' => $this->quotation->id,
                                'supplierId' => $supplier->id,
                            ])"
                        >
                            Lançar {{ $supplier->supplier?->trade_name ?: $supplier->supplier?->legal_name }}
                        </x-filament::button>
                    @endforeach
                </div>

                <div class="comparison-wrap">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th class="min-w-[300px] text-left">Produto</th>
                                <th class="w-32 text-right">Quantidade</th>
                                @foreach ($this->quotation->suppliers as $supplier)
                                    <th class="supplier-card">
                                        <span class="supplier-name">
                                            {{ $supplier->supplier?->trade_name ?: $supplier->supplier?->legal_name }}
                                        </span>
                                        <span class="supplier-meta">
                                            Frete: R$ {{ number_format((float) $supplier->freight_value, 2, ',', '.') }}
                                        </span>
                                        <span class="supplier-meta">
                                            {{ $supplier->delivery_days ? $supplier->delivery_days . ' dia(s)' : 'Prazo não informado' }}
                                        </span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($this->quotation->items as $item)
                                @php
                                    $proposals = $this->quotation->suppliers
                                        ->map(fn ($supplier) => $supplier->items
                                            ->firstWhere('quotation_item_id', $item->id))
                                        ->filter();

                                    $values = $proposals
                                        ->map(fn ($proposal) => (float) $proposal->total_value)
                                        ->filter(fn ($value) => $value > 0);

                                    $minimum = $values->isNotEmpty() ? $values->min() : null;
                                @endphp

                                <tr>
                                    <td>
                                        <div class="font-bold text-white">{{ $item->product?->code }}</div>
                                        <div class="mt-1 text-sm leading-5 text-gray-400">
                                            {{ $item->product?->name }}
                                        </div>
                                    </td>

                                    <td class="text-right font-semibold text-gray-200">
                                        {{ number_format((float) $item->quantity, 4, ',', '.') }}
                                    </td>

                                    @foreach ($this->quotation->suppliers as $supplier)
                                        @php
                                            $proposal = $supplier->items
                                                ->firstWhere('quotation_item_id', $item->id);

                                            $total = $proposal ? (float) $proposal->total_value : null;
                                            $isBest = $total !== null
                                                && $total > 0
                                                && $minimum !== null
                                                && $total === $minimum;
                                        @endphp

                                        <td>
                                            @if ($proposal)
                                                <div class="proposal-card {{ $isBest ? 'best' : '' }}">
                                                    <div class="proposal-total">
                                                        R$ {{ number_format($total, 2, ',', '.') }}
                                                    </div>

                                                    <div class="mt-2 text-xs text-gray-400">
                                                        Unitário:
                                                        R$ {{ number_format((float) $proposal->unit_value, 4, ',', '.') }}
                                                    </div>

                                                    @if ((float) $proposal->discount_value > 0)
                                                        <div class="mt-1 text-xs text-gray-400">
                                                            Desconto:
                                                            R$ {{ number_format((float) $proposal->discount_value, 2, ',', '.') }}
                                                        </div>
                                                    @endif

                                                    @if ($isBest)
                                                        <span class="best-badge">Menor valor</span>
                                                    @endif

                                                    <div class="mt-4">
                                                        <x-filament::button
                                                            size="xs"
                                                            :color="$proposal->is_selected ? 'success' : 'gray'"
                                                            wire:click="selectWinner('{{ $item->id }}', '{{ $proposal->id }}')"
                                                        >
                                                            {{ $proposal->is_selected ? 'Selecionado' : 'Selecionar' }}
                                                        </x-filament::button>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="no-proposal">
                                                    Proposta não lançada
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th class="text-left" colspan="2">Total da proposta</th>
                                @foreach ($this->quotation->suppliers as $supplier)
                                    <th class="text-center">
                                        <div class="text-base font-extrabold text-white">
                                            R$ {{ number_format((float) $supplier->total_value, 2, ',', '.') }}
                                        </div>
                                        <div class="supplier-meta">
                                            {{ $supplier->paymentCondition?->name ?: 'Condição não informada' }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($this->selectedAssetItems->isNotEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-500/40 bg-amber-500/5 p-5">
                        <div class="mb-4">
                            <div class="text-base font-bold text-white">Leituras dos ativos</div>
                            <div class="mt-1 text-sm text-gray-400">
                                Informe a leitura atual somente para os itens aplicados diretamente em ativos.
                            </div>
                        </div>

                        <div class="grid gap-4 xl:grid-cols-2">
                            @foreach ($this->selectedAssetItems as $item)
                                <div class="rounded-xl border border-gray-700 bg-gray-900/60 p-4">
                                    <div class="font-bold text-white">
                                        {{ $item->asset?->prefix ?: 'Ativo' }}
                                        — {{ $item->asset?->plate ?: $item->asset?->name }}
                                    </div>

                                    <div class="mt-1 text-sm text-gray-400">
                                        {{ $item->product?->code }} — {{ $item->product?->name }}
                                    </div>

                                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-gray-200">
                                                Medidor
                                            </label>
                                            <select
                                                wire:model.live="meterReadings.{{ $item->id }}.meter_type"
                                                class="careca-select"
                                            >
                                                <option value="odometer">Hodômetro</option>
                                                <option value="hourmeter">Horímetro</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-gray-200">
                                                Leitura atual
                                            </label>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                wire:model.live.debounce.250ms="meterReadings.{{ $item->id }}.meter_reading"
                                                class="careca-select"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-gray-200">
                                                Data e hora
                                            </label>
                                            <input
                                                type="datetime-local"
                                                wire:model.live="meterReadings.{{ $item->id }}.meter_recorded_at"
                                                class="careca-select"
                                            >
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <x-filament::button wire:click="generateOrders" icon="heroicon-o-shopping-cart">
                        Gerar Ordem(ns) de Compra
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
