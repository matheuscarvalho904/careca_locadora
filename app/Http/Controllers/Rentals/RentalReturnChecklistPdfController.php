<?php

namespace App\Http\Controllers\Rentals;

use App\Http\Controllers\Controller;
use App\Models\RentalReturn;
use App\Services\Rentals\ChecklistDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class RentalReturnChecklistPdfController extends Controller
{
    public function __invoke(
        RentalReturn $rentalReturn,
        ChecklistDocumentService $documents,
    ): Response {
        abort_unless(auth()->check(), 403);

        $data = $documents->returnData($rentalReturn);

        return Pdf::loadView('pdf.rental-checklist-premium', $data)
            ->setPaper('a4', 'portrait')
            ->stream("checklist-devolucao-{$rentalReturn->number}.pdf");
    }
}
