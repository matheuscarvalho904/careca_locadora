<?php

namespace App\Filament\Widgets;

use App\Models\RentalInvoice;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Faturamento das faturas de locação';

    protected ?string $description = 'Valores emitidos nos últimos seis meses';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset): CarbonInterface =>
                now()->startOfMonth()->subMonths($offset)
            );

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento',
                    'data' => $months
                        ->map(fn (CarbonInterface $month): float =>
                            (float) RentalInvoice::query()
                                ->whereNotIn('status', ['draft', 'cancelled'])
                                ->whereYear('issued_at', $month->year)
                                ->whereMonth('issued_at', $month->month)
                                ->sum('total_value')
                        )
                        ->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.18)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $months
                ->map(fn (CarbonInterface $month): string =>
                    ucfirst(
                        $month
                            ->locale('pt_BR')
                            ->translatedFormat('M/y')
                    )
                )
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
