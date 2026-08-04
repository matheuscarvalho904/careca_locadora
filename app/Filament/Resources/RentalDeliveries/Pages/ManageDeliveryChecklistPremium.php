<?php

namespace App\Filament\Resources\RentalDeliveries\Pages;

use App\Filament\Concerns\InteractsWithChecklistPremium;
use App\Filament\Resources\RentalDeliveries\RentalDeliveryResource;
use App\Models\RentalDamageMark;
use App\Models\RentalDamagePhoto;
use App\Models\RentalDelivery;
use Filament\Resources\Pages\Page;

class ManageDeliveryChecklistPremium extends Page
{
    use InteractsWithChecklistPremium;

    protected static string $resource = RentalDeliveryResource::class;

    protected string $view =
        'filament.resources.rental-deliveries.pages.manage-delivery-checklist-premium';

    public RentalDelivery $record;

    public function mount(RentalDelivery $record): void
    {
        $this->record = $record;
        $this->reloadPremiumChecklist();
    }

    public function getTitle(): string
    {
        return "Checklist Premium - {$this->record->number}";
    }

    protected function signatureDirectory(): string
    {
        return 'rental-deliveries/signatures';
    }

    protected function findAuthorizedDamageMark(
        string $markId,
    ): RentalDamageMark {
        return RentalDamageMark::query()
            ->whereKey($markId)
            ->where('organization_id', $this->record->organization_id)
            ->whereHasMorph(
                'inspectable',
                [\App\Models\RentalDeliveryItem::class],
                fn ($query) => $query
                    ->where('delivery_id', $this->record->id)
            )
            ->firstOrFail();
    }

    protected function authorizedDamagePhoto(
        string $photoId,
    ): RentalDamagePhoto {
        return RentalDamagePhoto::query()
            ->whereKey($photoId)
            ->whereHas(
                'damageMark',
                fn ($query) => $query
                    ->where('organization_id', $this->record->organization_id)
                    ->whereHasMorph(
                        'inspectable',
                        [\App\Models\RentalDeliveryItem::class],
                        fn ($query) => $query
                            ->where('delivery_id', $this->record->id)
                    )
            )
            ->firstOrFail();
    }

    protected function reloadPremiumChecklist(): void
    {
        $this->record->refresh()->load([
            'contract.customer',
            'contract.company',
            'contract.branch',
            'items.asset.category',
            'items.damageMarks.templateView',
            'items.damageMarks.photos',
        ]);
    }
}
