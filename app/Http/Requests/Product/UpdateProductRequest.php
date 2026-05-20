<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'store_id' => ['sometimes', 'integer', 'exists:stores,id'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'product_condition_id' => ['nullable', 'integer', 'exists:product_conditions,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product)],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'description' => ['sometimes', 'string', 'max:10000'],
            'specs' => ['nullable', 'array'],
            'specs.*' => ['nullable', 'string', 'max:255'],
            'price_amount' => ['sometimes', 'integer', 'min:0'],
            'original_price_amount' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'location_country_code' => ['nullable', 'string', 'size:2'],
            'location_state' => ['nullable', 'string', 'max:255'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['draft', 'pending', 'published', 'scheduled', 'sold', 'inactive', 'archived'])],
            'visibility' => ['nullable', Rule::in(['public', 'followers_only', 'private'])],
            'allow_offers' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'schedule_at' => ['nullable', 'date', 'after:now'],
            'auto_post' => ['nullable', 'string', 'max:50'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.label' => ['required_with:variants.*', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.attributes.*' => ['nullable', 'string', 'max:255'],
            'variants.*.price_amount' => ['nullable', 'integer', 'min:0'],
            'variants.*.original_price_amount' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.sort_order' => ['nullable', 'integer'],
            'variants.*.is_default' => ['nullable', 'boolean'],
        ];
    }
}
