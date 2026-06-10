<?php

namespace App\Http\Requests\Social;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'platform' => ['nullable', Rule::in(['facebook', 'tiktok'])],
            'destination' => ['nullable', 'string', 'max:255'],
        ];
    }
}
