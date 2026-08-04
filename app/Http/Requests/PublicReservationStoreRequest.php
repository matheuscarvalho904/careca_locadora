<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicReservationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => ['required', 'uuid'],
            'branch_id' => ['nullable', 'uuid'],
            'category_id' => ['required', 'uuid'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'commercial_item_ids' => ['nullable', 'array'],
            'commercial_item_ids.*' => ['uuid'],
            'coupon_code' => ['nullable', 'string', 'max:60'],

            'customer.name' => ['required', 'string', 'max:200'],
            'customer.document' => ['required', 'string', 'max:20'],
            'customer.email' => ['required', 'email', 'max:200'],
            'customer.phone' => ['required', 'string', 'max:30'],

            'accept_terms' => ['accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $customer = (array) $this->input('customer', []);

        $customer['document'] = preg_replace(
            '/\D+/',
            '',
            (string) ($customer['document'] ?? '')
        );

        $customer['phone'] = preg_replace(
            '/\D+/',
            '',
            (string) ($customer['phone'] ?? '')
        );

        $this->merge(['customer' => $customer]);
    }
}
