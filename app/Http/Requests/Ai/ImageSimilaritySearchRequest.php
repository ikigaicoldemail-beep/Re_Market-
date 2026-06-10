<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class ImageSimilaritySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'product_image_id' => ['nullable', 'integer', 'exists:product_images,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'top_k' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $this->hasFile('image') && ! $this->filled('product_image_id') && ! $this->filled('product_id')) {
                    $validator->errors()->add('image', 'An image file, product_image_id, or product_id is required.');
                }
            },
        ];
    }
}
