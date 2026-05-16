<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_condition_id' => ['nullable', 'integer', 'exists:product_conditions,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'description' => ['required', 'string', 'max:10000'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'location_country_code' => ['nullable', 'string', 'size:2'],
            'location_state' => ['nullable', 'string', 'max:255'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['draft', 'pending', 'published', 'sold', 'inactive', 'archived'])],
            'visibility' => ['nullable', Rule::in(['public', 'followers_only', 'private'])],
            'allow_offers' => ['nullable', 'boolean'],
            'schedule_at' => ['nullable', 'date'],
            'auto_post' => ['nullable', 'string', 'max:50'],
            'images' => ['sometimes', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
