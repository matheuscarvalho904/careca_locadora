<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PublicCatalogQuoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'branch_id' => ['nullable','uuid'],
            'category_id' => ['required','uuid'],
            'starts_at' => ['required','date'],
            'ends_at' => ['required','date','after:starts_at'],
            'commercial_item_ids' => ['nullable','array'],
            'commercial_item_ids.*' => ['uuid'],
            'coupon_code' => ['nullable','string','max:60'],
            'customer_id' => ['nullable','uuid'],
        ];
    }
}
