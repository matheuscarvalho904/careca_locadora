<?php
namespace App\Http\Controllers\Api;
use App\Data\Rentals\ReservationSearch;
use App\Domain\Reservations\ReservationAvailabilityEngine;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicCatalogAvailabilityRequest;
use App\Http\Requests\PublicCatalogQuoteRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Branch;
use App\Services\Rentals\RentalCommercialPricingService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class PublicCatalogController extends Controller
{
    public function branches(): JsonResponse
    {
        $data = Branch::query()
            ->withoutOrganizationScope()
            ->where('organization_id', $this->organizationId())
            ->orderBy('trade_name')
            ->get()
            ->map(fn (Branch $branch): array => [
                'id' => $branch->id,
                'name' => $branch->trade_name ?: $branch->name,
                'city' => $branch->city,
                'state' => $branch->state,
            ]);

        return response()->json(['data' => $data]);
    }

    public function categories(): JsonResponse
    {
        $data = AssetCategory::query()
            ->withoutOrganizationScope()
            ->where('organization_id', $this->organizationId())
            ->where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(fn (AssetCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'prefix' => $category->prefix,
                'metadata' => $category->metadata,
            ]);

        return response()->json(['data' => $data]);
    }

    public function availability(
        PublicCatalogAvailabilityRequest $request,
        ReservationAvailabilityEngine $engine,
    ): JsonResponse {
        $search = $this->search($request->validated());

        $assets = $engine->availableAssets(
            $search,
            $request->string('search')->toString() ?: null,
            60
        )->map(fn (Asset $asset): array => [
            'id' => $asset->id,
            'prefix' => $asset->prefix,
            'name' => $asset->name,
            'plate' => $asset->plate,
            'seats' => $asset->seats,
            'transmission' => $asset->transmission,
            'fuel_type' => $asset->fuel_type,
            'model_year' => $asset->model_year,
            'category' => [
                'id' => $asset->category?->id,
                'name' => $asset->category?->name,
            ],
            'branch' => [
                'id' => $asset->branch?->id,
                'name' => $asset->branch?->trade_name ?: $asset->branch?->name,
                'city' => $asset->branch?->city,
                'state' => $asset->branch?->state,
            ],
            'photos' => $asset->photos
                ->sortByDesc('is_featured')
                ->sortBy('display_order')
                ->map(fn ($photo): array => [
                    'path' => $photo->path,
                    'disk' => $photo->disk ?? 'public',
                    'featured' => (bool) $photo->is_featured,
                ])->values()->all(),
        ]);

        return response()->json(['data' => $assets, 'meta' => [
            'count' => $assets->count(),
            'starts_at' => $search->startsAt->toIso8601String(),
            'ends_at' => $search->endsAt->toIso8601String(),
        ]]);
    }

    public function quote(
        PublicCatalogQuoteRequest $request,
        RentalCommercialPricingService $pricing,
    ): JsonResponse {
        $data = $request->validated();
        $quote = $pricing->quote(
            $this->search($data),
            $data['commercial_item_ids'] ?? [],
            $data['coupon_code'] ?? null,
            $data['customer_id'] ?? null,
        );

        return response()->json(['data' => $quote]);
    }

    private function search(array $data): ReservationSearch
    {
        return ReservationSearch::fromArray([
            'organization_id' => $this->organizationId(),
            'branch_id' => $data['branch_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'preparation_minutes' => config('careca-public.preparation_minutes', 60),
        ]);
    }

    private function organizationId(): string
    {
        $id = config('careca-public.organization_id');

        if (blank($id)) {
            throw new ServiceUnavailableHttpException(
                null,
                'CARECA_PUBLIC_ORGANIZATION_ID nao configurado.'
            );
        }

        return (string) $id;
    }
}
