<?php

namespace App\Filament\Resources\RentalDeliveries\Pages;

use App\Filament\Resources\RentalDeliveries\RentalDeliveryResource;
use App\Models\RentalDamageMark;
use App\Models\RentalDelivery;
use App\Services\Rentals\DamageMapService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ManageDeliveryDamageMap extends Page
{
    protected static string $resource = RentalDeliveryResource::class;

    protected string $view =
        'filament.resources.rental-deliveries.pages.manage-delivery-damage-map';

    public RentalDelivery $record;

    public function mount(RentalDelivery $record): void
    {
        $this->record = $record->load([
            'items.asset.category',
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
            condition: 'preexisting',
        );

        $this->reloadMap();

        Notification::make()
            ->success()
            ->title('Avaria preexistente registrada')
            ->send();
    }

    public function deleteMark(string $markId): void
    {
        $mark = RentalDamageMark::query()
            ->whereKey($markId)
            ->where('organization_id', $this->record->organization_id)
            ->whereHasMorph(
                'inspectable',
                [\App\Models\RentalDeliveryItem::class],
                fn ($query) => $query->where('delivery_id', $this->record->id)
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
        return "Mapa de avarias — {$this->record->number}";
    }

    private function reloadMap(): void
    {
        $this->record->refresh()->load([
            'items.asset.category',
            'items.damageMarks.templateView',
            'items.damageMarks.photos',
        ]);
    }
}
