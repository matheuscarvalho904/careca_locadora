<?php

namespace App\Filament\Pages;

use App\Models\RentalReservationItem;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use UnitEnum;

class RentalAvailabilityCalendar extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-view-columns';
    protected static string | UnitEnum | null $navigationGroup = 'Locações';
    protected static ?string $navigationLabel = 'Agenda de disponibilidade';
    protected static ?string $title = 'Agenda de disponibilidade';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.rental-availability-calendar';

    public string $startDate;

    public function mount(): void
    {
        $this->startDate = now()->startOfDay()->toDateString();
    }

    public function getDaysProperty(): array
    {
        $start = CarbonImmutable::parse($this->startDate)->startOfDay();

        return collect(range(0, 13))
            ->map(fn (int $offset): CarbonImmutable => $start->addDays($offset))
            ->all();
    }

    public function getScheduleProperty()
    {
        $start = CarbonImmutable::parse($this->startDate)->startOfDay();
        $end = $start->addDays(14);

        return RentalReservationItem::query()
            ->with(['asset', 'reservation.customer'])
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->whereHas('reservation', fn ($query) =>
                $query->whereIn('status', [
                    'pending',
                    'confirmed',
                    'preparing',
                    'converted',
                ])
            )
            ->orderBy('starts_at')
            ->get()
            ->groupBy('asset_id');
    }
}
