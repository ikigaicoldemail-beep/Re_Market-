<?php

namespace App\Http\Requests\Social;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSocialPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', Rule::in(['facebook'])],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'social_account_id' => ['nullable', 'integer', 'exists:social_accounts,id'],
            'caption' => ['nullable', 'string', 'max:5000'],
            'publish_now' => ['nullable', 'boolean'],
        ];
    }
}
