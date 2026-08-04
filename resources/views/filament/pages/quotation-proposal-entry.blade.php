<x-filament-panels::page>
    <style>
        .careca-select,
        .careca-field {
            width: 100%;
            min-height: 44px;
            border: 1px solid rgb(63 63 70);
            border-radius: 0.75rem;
            background: rgb(24 24 27);
            color: rgb(244 244 245);
            padding: 0.65rem 0.85rem;
            outline: none;
        }

        .careca-select {
            padding-right: 2.5rem;
        }

        .careca-select:focus,
        .careca-field:focus {
            border-color: rgb(245 158 11);
            box-shadow: 0 0 0 1px rgb(245 158 11);
        }

        .careca-select:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        .careca-select option {
            background: #ffffff;
            color: #111827;
        }

        .careca-label {
            display: block;
            margin-bottom: .45rem;
            font-size: .875rem;
            font-weight: 600;
            color: rgb(228 228 231);
        }

        .proposal-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .span-2 { grid-column: span 2 / span 2; }
        .span-3 { grid-column: span 3 / span 3; }
        .span-4 { grid-column: span 4 / span 4; }
        .span-6 { grid-column: span 6 / span 6; }
        .span-12 { grid-column: span 12 / span 12; }

        .items-desktop {
            display: block;
        }

        .items-mobile {
            display: none;
        }

        .careca-table-wrap {
            overflow-x: auto;
            border: 1px solid rgb(63 63 70);
            border-radius: 1rem;
            background: rgba(9, 9, 11, .35);
        }

        .careca-table {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .careca-table th {
            background: rgb(39 39 42);
            color: rgb(244 244 245);
            padding: .9rem 1rem;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .035em;
            border-bottom: 1px solid rgb(63 63 70);
            white-space: nowrap;
        }

        .careca-table td {
            padding: .9rem 1rem;
            border-bottom: 1px solid rgb(39 39 42);
            vertical-align: middle;
        }

        .careca-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .careca-table tbody tr:hover td {
            background: rgba(39, 39, 42, .35);
        }

        .product-code {
            font-weight: 700;
            color: white;
        }

        .product-name {
            margin-top: .2rem;
            color: rgb(161 161 170);
            font-size: .85rem;
            line-height: 1.2rem;
        }

        .summary-card {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            border: 1px solid rgb(63 63 70);
            border-radius: 1rem;
            background: rgba(24, 24, 27, .65);
            padding: 1rem;
        }

        .summary-item {
            border-radius: .8rem;
            background: rgba(39, 39, 42, .7);
            padding: .9rem 1rem;
        }

        .summary-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgb(161 161 170);
        }

        .summary-value {
            margin-top: .25rem;
            font-size: 1rem;
            font-weight: 800;
            color: white;
        }

        @media (max-width: 1279px) {
            .span-2,
            .span-3,
            .span-4 {
                grid-column: span 6 / span 6;
            }

            .summary-card {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 900px) {
            .items-desktop {
                display: none;
            }

            .items-mobile {
                display: grid;
                gap: 1rem;
            }

            .proposal-item-card {
                border: 1px solid rgb(63 63 70);
                border-radius: 1rem;
                background: rgba(24, 24, 27, .7);
                padding: 1rem;
            }

            .proposal-item-grid {
                display: grid;
                gap: .9rem;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-top: 1rem;
            }
        }

        @media (max-width: 640px) {
            .span-2,
            .span-3,
            .span-4,
            .span-6 {
                grid-column: span 12 / span 12;
            }

            .proposal-item-grid,
            .summary-card {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Selecionar cotação e fornecedor</x-slot>
            <x-slot name="description">
                Escolha a cotação e o fornecedor para lançar ou alterar a proposta comercial.
            </x-slot>

            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <label class="careca-label">Cotação</label>
                    <select wire:model.live="quotationId" class="careca-select">
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
                </div>

                <div>
                    <label class="careca-label">Fornecedor</label>
                    <select
                        wire:model.live="supplierId"
                        class="careca-select"
                        @disabled(blank($quotationId))
                    >
                        <option value="">Selecione um fornecedor...</option>
                        @foreach ($this->suppliers as $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->supplier?->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-filament::section>

        @if ($this->quotation && $supplierId)
            <x-filament::section>
                <x-slot name="heading">Dados gerais da proposta</x-slot>
                <x-slot name="description">
                    Informe prazos, condições e valores complementares da proposta.
                </x-slot>

                <div class="proposal-grid">
                    <div class="span-3">
                        <label class="careca-label">Data da proposta</label>
                        <input type="date" wire:model="proposalDate" class="careca-field">
                    </div>

                    <div class="span-3">
                        <label class="careca-label">Validade da proposta</label>
                        <input type="date" wire:model="proposalValidUntil" class="careca-field">
                    </div>

                    <div class="span-3">
                        <label class="careca-label">Prazo geral de entrega</label>
                        <input type="number" min="0" wire:model="deliveryDays" class="careca-field">
                    </div>

                    <div class="span-3">
                        <label class="careca-label">Condição de pagamento</label>
                        <select wire:model="paymentConditionId" class="careca-select">
                            <option value="">Selecione...</option>
                            @foreach ($this->paymentConditions as $condition)
                                <option value="{{ $condition->id }}">
                                    {{ $condition->code }} — {{ $condition->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="span-4">
                        <label class="careca-label">Frete</label>
                        <input type="number" min="0" step="0.01" wire:model="freightValue" class="careca-field">
                    </div>

                    <div class="span-4">
                        <label class="careca-label">Desconto geral</label>
                        <input type="number" min="0" step="0.01" wire:model="discountValue" class="careca-field">
                    </div>

                    <div class="span-4">
                        <label class="careca-label">Outras despesas</label>
                        <input type="number" min="0" step="0.01" wire:model="additionalValue" class="careca-field">
                    </div>

                    <div class="span-12">
                        <label class="careca-label">Observações</label>
                        <textarea wire:model="notes" rows="4" class="careca-field"></textarea>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Valores por produto</x-slot>
                <x-slot name="description">
                    Informe valor unitário, desconto e prazo específico de cada produto.
                </x-slot>

                <div class="items-desktop">
                    <div class="careca-table-wrap">
                        <table class="careca-table">
                            <thead>
                                <tr>
                                    <th class="min-w-[260px] text-left">Produto</th>
                                    <th class="w-28 text-right">Quantidade</th>
                                    <th class="w-40 text-right">Valor unitário</th>
                                    <th class="w-36 text-right">Desconto</th>
                                    <th class="w-28 text-right">Prazo</th>
                                    <th class="min-w-[240px] text-left">Observações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($this->quotation->items as $item)
                                    <tr>
                                        <td>
                                            <div class="product-code">{{ $item->product?->code }}</div>
                                            <div class="product-name">{{ $item->product?->name }}</div>
                                        </td>

                                        <td class="text-right font-semibold text-gray-200">
                                            {{ number_format((float) $item->quantity, 4, ',', '.') }}
                                        </td>

                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.0001"
                                                wire:model="items.{{ $item->id }}.unit_value"
                                                class="careca-field text-right"
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                wire:model="items.{{ $item->id }}.discount_value"
                                                class="careca-field text-right"
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                wire:model="items.{{ $item->id }}.delivery_days"
                                                class="careca-field text-right"
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                wire:model="items.{{ $item->id }}.notes"
                                                class="careca-field"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="items-mobile">
                    @foreach ($this->quotation->items as $item)
                        <div class="proposal-item-card">
                            <div class="product-code">{{ $item->product?->code }}</div>
                            <div class="product-name">{{ $item->product?->name }}</div>

                            <div class="mt-2 text-sm font-semibold text-gray-300">
                                Quantidade: {{ number_format((float) $item->quantity, 4, ',', '.') }}
                            </div>

                            <div class="proposal-item-grid">
                                <div>
                                    <label class="careca-label">Valor unitário</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.0001"
                                        wire:model="items.{{ $item->id }}.unit_value"
                                        class="careca-field"
                                    >
                                </div>

                                <div>
                                    <label class="careca-label">Desconto</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        wire:model="items.{{ $item->id }}.discount_value"
                                        class="careca-field"
                                    >
                                </div>

                                <div>
                                    <label class="careca-label">Prazo</label>
                                    <input
                                        type="number"
                                        min="0"
                                        wire:model="items.{{ $item->id }}.delivery_days"
                                        class="careca-field"
                                    >
                                </div>

                                <div>
                                    <label class="careca-label">Observações</label>
                                    <input
                                        type="text"
                                        wire:model="items.{{ $item->id }}.notes"
                                        class="careca-field"
                                    >
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 summary-card">
                    <div class="summary-item">
                        <div class="summary-label">Produtos</div>
                        <div class="summary-value">{{ $this->quotation->items->count() }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Fornecedor</div>
                        <div class="summary-value">
                            {{ $this->suppliers->firstWhere('id', $supplierId)?->supplier?->trade_name
                                ?: $this->suppliers->firstWhere('id', $supplierId)?->supplier?->legal_name
                                ?: '—' }}
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Cotação</div>
                        <div class="summary-value">{{ $this->quotation->number }}</div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <x-filament::button
                        tag="a"
                        color="gray"
                        icon="heroicon-o-arrows-right-left"
                        :href="\App\Filament\Pages\QuotationComparison::getUrl([
                            'quotationId' => $this->quotation->id,
                        ])"
                    >
                        Voltar ao comparativo
                    </x-filament::button>

                    <x-filament::button wire:click="saveProposal" icon="heroicon-o-check">
                        Salvar proposta
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
