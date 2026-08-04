<?php

namespace App\Filament\Resources\RentalReturns\Pages;

use App\Filament\Concerns\InteractsWithChecklistPremium;
use App\Filament\Resources\RentalReturns\RentalReturnResource;
use App\Models\RentalDamageMark;
use App\Models\RentalDamagePhoto;
use App\Models\RentalReturn;
use Filament\Resources\Pages\Page;

class ManageReturnChecklistPremium extends Page
{
    use InteractsWithChecklistPremium;

    protected static string $resource = RentalReturnResource::class;

    protected string $view =
        'filament.resources.rental-returns.pages.manage-return-checklist-premium';

    public RentalReturn $record;

    public function mount(RentalReturn $record): void
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
        return 'rental-returns/signatures';
    }

    protected function findAuthorizedDamageMark(
        string $markId,
    ): RentalDamageMark {
        return RentalDamageMark::query()
            ->whereKey($markId)
            ->where('organization_id', $this->record->organization_id)
            ->whereHasMorph(
                'inspectable',
                [\App\Models\RentalReturnItem::class],
                fn ($query) => $query
                    ->where('return_id', $this->record->id)
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
                        [\App\Models\RentalReturnItem::class],
                        fn ($query) => $query
                            ->where('return_id', $this->record->id)
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
            'delivery',
            'items.asset.category',
            'items.deliveryItem.damageMarks.templateView',
            'items.deliveryItem.damageMarks.photos',
            'items.damageMarks.templateView',
            'items.damageMarks.photos',
        ]);
    }
}
