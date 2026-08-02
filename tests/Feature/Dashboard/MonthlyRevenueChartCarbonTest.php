<?php

it('aceita Carbon e CarbonImmutable no gráfico mensal', function (): void {
    $path = app_path('Filament/Widgets/MonthlyRevenueChart.php');
    $source = file_get_contents($path);

    expect($source)
        ->toContain('use Carbon\CarbonInterface;')
        ->toContain('fn (int $offset): CarbonInterface')
        ->toContain('fn (CarbonInterface $month): float')
        ->toContain('fn (CarbonInterface $month): string');
});
