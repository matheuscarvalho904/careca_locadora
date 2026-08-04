<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Services\Documents\ProcurementDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class ServiceOrderPdfController extends Controller
{
    public function __invoke(
        ServiceOrder $serviceOrder,
        ProcurementDocumentService $documents,
    ): Response {
        abort_unless(auth()->check(), 403);

        return Pdf::loadView(
            'pdf.procurement-order-premium',
            $documents->serviceOrderData($serviceOrder)
        )
            ->setPaper('a4', 'portrait')
            ->stream("{$serviceOrder->number}.pdf");
    }
}
