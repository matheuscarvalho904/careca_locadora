<?php
namespace App\Filament\Widgets;
use App\Models\AccountReceivable;
use App\Models\Asset;
use App\Models\RentalContract;
use App\Models\RentalInvoice;
use App\Models\RentalReservation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class ExecutiveStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected function getStats(): array
    {
        $activeAssets = Asset::query()->where('status','active')->count();
        $availableAssets = Asset::query()->where('status','active')->where('rental_status','available')->count();
        $rentedAssets = Asset::query()->where('status','active')->where('rental_status','rented')->count();
        $occupancyRate = $activeAssets > 0 ? round(($rentedAssets / $activeAssets) * 100, 1) : 0;
        $futureReservations = RentalReservation::query()->whereDate('pickup_expected_at','>=',today())->whereNotIn('status',['completed','cancelled'])->count();
        $activeContracts = RentalContract::query()->where('status','active')->count();
        $monthlyRevenue = (float) RentalInvoice::query()->whereNotIn('status',['draft','cancelled'])->whereYear('issued_at',now()->year)->whereMonth('issued_at',now()->month)->sum('total_value');
        $openReceivables = (float) AccountReceivable::query()->whereNotIn('status',['paid','cancelled'])->sum('open_value');
        $overdueReceivables = (float) AccountReceivable::query()->whereNotIn('status',['paid','cancelled'])->whereDate('due_at','<',today())->sum('open_value');
        return [
            Stat::make('Ativos disponíveis', number_format($availableAssets,0,',','.'))->description("{$activeAssets} ativos cadastrados")->descriptionIcon('heroicon-m-truck')->color('success'),
            Stat::make('Ativos em locação', number_format($rentedAssets,0,',','.'))->description("Ocupação de {$occupancyRate}%")->descriptionIcon('heroicon-m-key')->color('info'),
            Stat::make('Reservas futuras', number_format($futureReservations,0,',','.'))->description('Retiradas programadas')->descriptionIcon('heroicon-m-calendar-days')->color('warning'),
            Stat::make('Contratos ativos', number_format($activeContracts,0,',','.'))->description('Locações em andamento')->descriptionIcon('heroicon-m-document-text')->color('primary'),
            Stat::make('Faturamento do mês', self::money($monthlyRevenue))->description(now()->translatedFormat('F \d\e Y'))->descriptionIcon('heroicon-m-banknotes')->color('success'),
            Stat::make('Contas em aberto', self::money($openReceivables))->description('Saldo total a receber')->descriptionIcon('heroicon-m-wallet')->color('warning'),
            Stat::make('Títulos vencidos', self::money($overdueReceivables))->description('Necessitam de cobrança')->descriptionIcon('heroicon-m-exclamation-triangle')->color($overdueReceivables > 0 ? 'danger' : 'success'),
            Stat::make('Taxa de ocupação', number_format($occupancyRate,1,',','.') . '%')->description("{$rentedAssets} de {$activeAssets} ativos")->descriptionIcon('heroicon-m-chart-bar')->color($occupancyRate >= 70 ? 'success' : 'gray'),
        ];
    }
    private static function money(float $value): string { return 'R$ ' . number_format($value,2,',','.'); }
}
