<?php

$file = dirname(__DIR__) . '/resources/views/filament/pages/quotation-comparison.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    'wire:model="meterReadings.{{ $item->id }}.meter_type"',
    'wire:model.live="meterReadings.{{ $item->id }}.meter_type"',
    $content
);

$content = str_replace(
    'wire:model="meterReadings.{{ $item->id }}.meter_reading"',
    'wire:model.live.debounce.250ms="meterReadings.{{ $item->id }}.meter_reading"',
    $content
);

$content = str_replace(
    'wire:model="meterReadings.{{ $item->id }}.meter_recorded_at"',
    'wire:model.live="meterReadings.{{ $item->id }}.meter_recorded_at"',
    $content
);

file_put_contents($file, $content);
