<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
<<<<<<<< HEAD:app/Models/ProductVariant.php
        'label',
        'sku',
        'attributes',
        'price_amount',
        'original_price_amount',
        'stock_quantity',
        'sort_order',
        'is_default',
========
        'product_variant_id',
        'quantity',
        'unit_price_amount',
        'line_total_amount',
>>>>>>>> lyhour7/PPM:app/Models/CartItem.php
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
