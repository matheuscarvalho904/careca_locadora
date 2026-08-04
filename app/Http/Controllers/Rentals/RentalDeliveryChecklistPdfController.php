<?php

namespace App\Http\Controllers\Rentals;

use App\Http\Controllers\Controller;
use App\Models\RentalDelivery;
use App\Services\Rentals\ChecklistDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class RentalDeliveryChecklistPdfController extends Controller
{
    public function __invoke(
        RentalDelivery $delivery,
        ChecklistDocumentService $documents,
    ): Response {
        abort_unless(auth()->check(), 403);

        $data = $documents->deliveryData($delivery);

        return Pdf::loadView('pdf.rental-checklist-premium', $data)
            ->setPaper('a4', 'portrait')
            ->stream("checklist-entrega-{$delivery->number}.pdf");
    }
}
