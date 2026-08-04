<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PublicCatalogAvailabilityRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'branch_id' => ['nullable','uuid'],
            'category_id' => ['nullable','uuid'],
            'starts_at' => ['required','date'],
            'ends_at' => ['required','date','after:starts_at'],
            'search' => ['nullable','string','max:100'],
        ];
    }
}
