<?php

namespace App\Filament\Resources\RentalReturns\Pages;

use App\Filament\Resources\RentalReturns\RentalReturnResource;
use App\Models\RentalDamageMark;
use App\Models\RentalReturn;
use App\Services\Rentals\DamageMapService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ManageReturnDamageMap extends Page
{
    protected static string $resource = RentalReturnResource::class;

    protected string $view =
        'filament.resources.rental-returns.pages.manage-return-damage-map';

    public RentalReturn $record;

    public function mount(RentalReturn $record): void
    {
        $this->record = $record->load([
            'items.asset.category',
            'items.deliveryItem.damageMarks.templateView',
            'items.deliveryItem.damageMarks.photos',
            'items.damageMarks.templateView',
            'items.damageMarks.photos',
        ]);
    }

    public function addMark(array $data): void
    {
        $item = $this->record->items()
            ->whereKey($data['item_id'])
            ->firstOrFail();

        app(DamageMapService::class)->createMark(
            item: $item,
            data: $data,
            condition: $data['condition'] ?? 'new',
        );

        $this->reloadMap();

        Notification::make()
            ->success()
            ->title('Avaria da devolução registrada')
            ->send();
    }

    public function deleteMark(string $markId): void
    {
        $mark = RentalDamageMark::query()
            ->whereKey($markId)
            ->where('organization_id', $this->record->organization_id)
            ->whereHasMorph(
                'inspectable',
                [\App\Models\RentalReturnItem::class],
                fn ($query) => $query->where('return_id', $this->record->id)
            )
            ->firstOrFail();

        app(DamageMapService::class)->deleteMark($mark);

        $this->reloadMap();

        Notification::make()
            ->success()
            ->title('Marcação removida')
            ->send();
    }

    public function getTitle(): string
    {
        return "Comparação de avarias — {$this->record->number}";
    }

    private function reloadMap(): void
    {
        $this->record->refresh()->load([
            'items.asset.category',
            'items.deliveryItem.damageMarks.templateView',
            'items.deliveryItem.damageMarks.photos',
            'items.damageMarks.templateView',
            'items.damageMarks.photos',
        ]);
    }
}
