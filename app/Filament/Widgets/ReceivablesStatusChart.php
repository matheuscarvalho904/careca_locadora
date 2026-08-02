<?php
namespace App\Filament\Widgets;
use App\Models\AccountReceivable;
use Filament\Widgets\ChartWidget;
class ReceivablesStatusChart extends ChartWidget
{
    protected ?string $heading = 'Carteira de contas a receber';
    protected ?string $description = 'Distribuição do saldo financeiro';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = ['md'=>2,'xl'=>2];
    protected function getData(): array
    {
        $open=(float)AccountReceivable::query()->where('status','open')->sum('open_value');
        $overdue=(float)AccountReceivable::query()->where('status','overdue')->sum('open_value');
        $partial=(float)AccountReceivable::query()->where('status','partially_paid')->sum('open_value');
        $paid=(float)AccountReceivable::query()->where('status','paid')->sum('paid_value');
        return ['datasets'=>[['label'=>'Valores','data'=>[$open,$overdue,$partial,$paid],'backgroundColor'=>['#f59e0b','#ef4444','#3b82f6','#10b981'],'borderWidth'=>0]],'labels'=>['Em aberto','Vencidas','Parcialmente recebidas','Recebidas']];
    }
    protected function getType(): string { return 'doughnut'; }
}
