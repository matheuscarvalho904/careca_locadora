<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\RentalInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class RentalInvoicePdfController extends Controller
{
    public function __invoke(string $invoice): Response
    {
        $invoice = RentalInvoice::query()
            ->with([
                'organization',
                'customer',
                'contract.reservation',
                'contract.items.asset',
                'items.asset',
                'receivables',
            ])
            ->findOrFail($invoice);

        $logo = $this->imageDataUri(
            public_path('images/careca-locadora-logo.png')
        );

        $data = [
            'invoice' => $invoice,
            'logo' => $logo,
            'organization' => $this->organizationData($invoice->organization),
            'customer' => $this->customerData($invoice->customer),
            'validationCode' => strtoupper(
                substr(hash('sha256', $invoice->id . '|' . $invoice->number), 0, 16)
            ),
        ];

        return Pdf::loadView('pdf.rental-invoice', $data)
            ->setPaper('a4')
            ->setOption([
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 150,
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ])
            ->download("Fatura-de-Locacao-{$invoice->number}.pdf");
    }

    private function organizationData(mixed $organization): array
    {
        return [
            'name' => $this->firstFilled($organization, [
                'legal_name',
                'trade_name',
                'name',
            ], 'Careca Locadora de Veículos'),
            'document' => $this->firstFilled($organization, [
                'document',
                'tax_id',
                'cnpj',
                'cpf_cnpj',
            ]),
            'email' => $this->firstFilled($organization, ['email']),
            'phone' => $this->firstFilled($organization, [
                'phone',
                'telephone',
                'mobile',
            ]),
            'address' => $this->address($organization),
        ];
    }

    private function customerData(mixed $customer): array
    {
        return [
            'name' => $this->firstFilled($customer, [
                'display_name',
                'legal_name',
                'trade_name',
                'name',
            ], 'Cliente não informado'),
            'legal_name' => $this->firstFilled($customer, [
                'legal_name',
                'company_name',
            ]),
            'document' => $this->firstFilled($customer, [
                'document',
                'tax_id',
                'cpf_cnpj',
                'cnpj',
                'cpf',
            ]),
            'email' => $this->firstFilled($customer, ['email']),
            'phone' => $this->firstFilled($customer, [
                'phone',
                'telephone',
                'mobile',
            ]),
            'address' => $this->address($customer),
        ];
    }

    private function address(mixed $model): ?string
    {
        if ($model === null) {
            return null;
        }

        $street = $this->firstFilled($model, [
            'address',
            'street',
            'street_name',
        ]);

        $number = $this->firstFilled($model, [
            'address_number',
            'number',
        ]);

        $district = $this->firstFilled($model, [
            'district',
            'neighborhood',
        ]);

        $city = $this->firstFilled($model, ['city']);
        $state = $this->firstFilled($model, ['state', 'uf']);
        $postalCode = $this->firstFilled($model, ['postal_code', 'zip_code', 'cep']);

        $parts = array_filter([
            trim(implode(', ', array_filter([$street, $number]))),
            $district,
            trim(implode(' - ', array_filter([$city, $state]))),
            $postalCode,
        ]);

        return $parts === [] ? null : implode(' | ', $parts);
    }

    private function firstFilled(
        mixed $model,
        array $attributes,
        ?string $fallback = null,
    ): ?string {
        if ($model === null) {
            return $fallback;
        }

        foreach ($attributes as $attribute) {
            $value = data_get($model, $attribute);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return $fallback;
    }

    private function imageDataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return sprintf(
            'data:%s;base64,%s',
            $mime,
            base64_encode((string) file_get_contents($path))
        );
    }
}
