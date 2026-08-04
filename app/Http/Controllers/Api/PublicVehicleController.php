<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class PublicVehicleController extends Controller
{
    public function show(string $asset): JsonResponse
    {
        $organizationId = config('careca-public.organization_id');

        if (blank($organizationId)) {
            throw new ServiceUnavailableHttpException(
                null,
                'CARECA_PUBLIC_ORGANIZATION_ID nao configurado.'
            );
        }

        $vehicle = Asset::query()
            ->withoutOrganizationScope()
            ->with(['category', 'branch', 'photos'])
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->findOrFail($asset);

        return response()->json([
            'data' => [
                'id' => $vehicle->id,
                'prefix' => $vehicle->prefix,
                'name' => $vehicle->name,
                'plate' => $vehicle->plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'model_year' => $vehicle->model_year,
                'seats' => $vehicle->seats,
                'doors' => $vehicle->doors,
                'transmission' => $vehicle->transmission,
                'fuel_type' => $vehicle->fuel_type,
                'color' => $vehicle->color,
                'category' => [
                    'id' => $vehicle->category?->id,
                    'name' => $vehicle->category?->name,
                ],
                'branch' => [
                    'id' => $vehicle->branch?->id,
                    'name' => $vehicle->branch?->trade_name
                        ?: $vehicle->branch?->name,
                    'city' => $vehicle->branch?->city,
                    'state' => $vehicle->branch?->state,
                ],
                'photos' => $vehicle->photos
                    ->sortByDesc('is_featured')
                    ->sortBy('display_order')
                    ->map(fn ($photo): array => [
                        'path' => $photo->path,
                        'featured' => (bool) $photo->is_featured,
                    ])
                    ->values()
                    ->all(),
                'metadata' => $vehicle->metadata,
            ],
        ]);
    }
}
