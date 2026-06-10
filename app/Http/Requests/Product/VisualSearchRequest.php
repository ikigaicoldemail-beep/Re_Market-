<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class VisualSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
