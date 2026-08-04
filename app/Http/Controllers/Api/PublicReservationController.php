<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicReservationStoreRequest;
use App\Services\Rentals\PublicReservationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class PublicReservationController extends Controller
{
    public function store(
        PublicReservationStoreRequest $request,
        PublicReservationService $reservations,
    ): JsonResponse {
        $organizationId = config('careca-public.organization_id');

        if (blank($organizationId)) {
            throw new ServiceUnavailableHttpException(
                null,
                'CARECA_PUBLIC_ORGANIZATION_ID nao configurado.'
            );
        }

        $reservation = $reservations->create(
            organizationId: (string) $organizationId,
            data: $request->validated(),
        );

        return response()->json([
            'message' => 'Reserva solicitada com sucesso.',
            'data' => [
                'id' => $reservation->id,
                'number' => $reservation->number,
                'status' => $reservation->status,
                'total_value' => (float) $reservation->total_value,
                'deposit_value' => (float) $reservation->deposit_value,
                'pickup_expected_at' =>
                    $reservation->pickup_expected_at?->toIso8601String(),
                'return_expected_at' =>
                    $reservation->return_expected_at?->toIso8601String(),
            ],
        ], 201);
    }
}
