<?php
namespace App\Filament\Widgets;
use App\Models\AccountReceivable;
use App\Models\RentalReservation;
use App\Models\RentalReturn;
use Filament\Widgets\Widget;
class UpcomingOperations extends Widget
{
    protected string $view = 'filament.widgets.upcoming-operations';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected function getViewData(): array
    {
        $reservations=RentalReservation::query()->with('customer')->whereDate('pickup_expected_at','>=',today())->whereNotIn('status',['completed','cancelled'])->orderBy('pickup_expected_at')->limit(6)->get();
        $returns=RentalReturn::query()->with('contract.customer')->where('status','draft')->orderBy('scheduled_at')->limit(6)->get();
        $receivables=AccountReceivable::query()->with(['customer','invoice'])->whereNotIn('status',['paid','cancelled'])->orderBy('due_at')->limit(6)->get();
        return compact('reservations','returns','receivables');
    }
}
