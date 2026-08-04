<?php

$file = dirname(__DIR__) . '/resources/views/filament/pages/quotation-comparison.blade.php';
$content = file_get_contents($file);

if (! str_contains($content, 'Leituras dos ativos')) {
    $needle = <<<'BLADE'
                <div class="mt-6 flex justify-end">
                    <x-filament::button wire:click="generateOrders" icon="heroicon-o-shopping-cart">
                        Gerar Ordem(ns) de Compra
                    </x-filament::button>
                </div>
BLADE;

    $replacement = <<<'BLADE'
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
                                                wire:model="meterReadings.{{ $item->id }}.meter_type"
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
                                                wire:model="meterReadings.{{ $item->id }}.meter_reading"
                                                class="careca-select"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-gray-200">
                                                Data e hora
                                            </label>
                                            <input
                                                type="datetime-local"
                                                wire:model="meterReadings.{{ $item->id }}.meter_recorded_at"
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
BLADE;

    if (! str_contains($content, $needle)) {
        fwrite(STDERR, "Botão Gerar OC não encontrado no mapa comparativo.\n");
        exit(1);
    }

    $content = str_replace($needle, $replacement, $content);
}

file_put_contents($file, $content);
