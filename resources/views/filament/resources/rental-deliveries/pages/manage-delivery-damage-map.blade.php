<x-filament-panels::page>
    <div class="space-y-6">
        @foreach ($record->items as $item)
            @php
                $template = app(\App\Services\Rentals\DamageMapService::class)
                    ->templateFor($item);
            @endphp

            <x-damage-map-canvas
                :item="$item"
                :template="$template"
                :marks="$item->damageMarks"
                :legacy-marks="collect()"
                mode="delivery"
            />
        @endforeach
    </div>
</x-filament-panels::page>
