<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\Documents\ProcurementDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class PurchaseOrderPdfController extends Controller
{
    public function __invoke(
        PurchaseOrder $purchaseOrder,
        ProcurementDocumentService $documents,
    ): Response {
        abort_unless(auth()->check(), 403);

        return Pdf::loadView(
            'pdf.procurement-order-premium',
            $documents->purchaseOrderData($purchaseOrder)
        )
            ->setPaper('a4', 'portrait')
            ->stream("{$purchaseOrder->number}.pdf");
    }
}
