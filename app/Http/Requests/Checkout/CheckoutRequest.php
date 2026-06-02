<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'provider' => ['nullable', 'string', 'max:100', Rule::in(['manual'])],
            'payment_method' => ['nullable', 'string', 'max:100', Rule::in(['cash_on_delivery'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
