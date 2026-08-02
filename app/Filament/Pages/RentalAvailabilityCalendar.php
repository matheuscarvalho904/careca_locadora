<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\RentalReservationItem;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
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

    public string $search = '';

    public string $statusFilter = 'all';

    public function mount(): void
    {
        $this->startDate = now()
            ->startOfDay()
            ->toDateString();
    }

    public function previousPeriod(): void
    {
        $this->startDate = CarbonImmutable::parse($this->startDate)
            ->subDays(14)
            ->toDateString();
    }

    public function nextPeriod(): void
    {
        $this->startDate = CarbonImmutable::parse($this->startDate)
            ->addDays(14)
            ->toDateString();
    }

    public function today(): void
    {
        $this->startDate = now()
            ->startOfDay()
            ->toDateString();
    }

    public function getDaysProperty(): array
    {
        $start = CarbonImmutable::parse($this->startDate)
            ->startOfDay();

        return collect(range(0, 13))
            ->map(
                fn (int $offset): CarbonImmutable =>
                    $start->addDays($offset)
            )
            ->all();
    }

    public function getScheduleProperty(): Collection
    {
        $start = CarbonImmutable::parse($this->startDate)
            ->startOfDay();

        $end = $start->addDays(14);

        $assets = Asset::query()
            ->where('status', 'active')
            ->when(
                filled($this->search),
                function ($query): void {
                    $search = trim($this->search);

                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('prefix', 'ilike', "%{$search}%")
                            ->orWhere('name', 'ilike', "%{$search}%")
                            ->orWhere('plate', 'ilike', "%{$search}%");
                    });
                }
            )
            ->orderBy('prefix')
            ->orderBy('name')
            ->get();

        $itemsByAsset = RentalReservationItem::query()
            ->with([
                'asset',
                'reservation.customer',
            ])
            ->whereIn('asset_id', $assets->pluck('id'))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->whereHas(
                'reservation',
                fn ($query) => $query->whereIn('status', [
                    'pending',
                    'confirmed',
                    'preparing',
                    'converted',
                    'active',
                    'in_rental',
                    'rented',
                ])
            )
            ->orderBy('starts_at')
            ->get()
            ->groupBy('asset_id');

        return $assets
            ->map(function (Asset $asset) use ($itemsByAsset): array {
                return [
                    'asset' => $asset,
                    'items' => $itemsByAsset->get(
                        $asset->id,
                        collect()
                    ),
                ];
            })
            ->filter(function (array $row): bool {
                if ($this->statusFilter === 'all') {
                    return true;
                }

                $hasReservation = $row['items']->isNotEmpty();

                return match ($this->statusFilter) {
                    'free' => ! $hasReservation,
                    'reserved' => $hasReservation,
                    default => true,
                };
            })
            ->values();
    }
}
