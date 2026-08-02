<?php

it('mantém todos os ativos ativos na agenda mesmo sem reserva', function (): void {
    $page = file_get_contents(
        app_path('Filament/Pages/RentalAvailabilityCalendar.php')
    );

    $view = file_get_contents(
        resource_path(
            'views/filament/pages/rental-availability-calendar.blade.php'
        )
    );

    expect($page)
        ->toContain('use App\Models\Asset;')
        ->toContain('Asset::query()')
        ->toContain("->where('status', 'active')")
        ->toContain("'asset' => \$asset")
        ->toContain("'items' => \$itemsByAsset->get(")
        ->toContain('public string $search')
        ->toContain('public string $statusFilter')
        ->toContain('previousPeriod')
        ->toContain('nextPeriod');

    expect($view)
        ->toContain('@forelse ($this->schedule as $row)')
        ->toContain("\$asset = \$row['asset']")
        ->toContain("\$items = \$row['items']")
        ->toContain('Somente livres')
        ->toContain('Com reservas no período')
        ->toContain('Ativo livre neste dia')
        ->not->toContain('LocaÃ§Ãµes')
        ->not->toContain('InÃ­cio');
});
