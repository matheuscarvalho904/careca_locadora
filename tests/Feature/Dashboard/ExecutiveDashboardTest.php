<?php
it('registra o dashboard executivo e seus widgets', function (): void {
    $paths=[app_path('Filament/Pages/Dashboard.php'),app_path('Filament/Widgets/ExecutiveStatsOverview.php'),app_path('Filament/Widgets/MonthlyRevenueChart.php'),app_path('Filament/Widgets/ReceivablesStatusChart.php'),app_path('Filament/Widgets/UpcomingOperations.php')];
    foreach($paths as $path){ expect(file_exists($path))->toBeTrue(); }
    expect(file_get_contents($paths[1]))->toContain("Stat::make('Ativos disponíveis'")->toContain("Stat::make('Faturamento do mês'")->toContain("Stat::make('Títulos vencidos'");
    expect(file_get_contents($paths[2]))->toContain("return 'line'");
    expect(file_get_contents($paths[3]))->toContain("return 'doughnut'");
});
