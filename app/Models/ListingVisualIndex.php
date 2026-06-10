<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingVisualIndex extends Model
{
    use HasFactory;

    protected $table = 'listing_visual_index';

    protected $fillable = [
        'listing_id',
        'product_image_id',
        'faiss_id',
    ];

    protected function casts(): array
    {
        return [
            'faiss_id' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'listing_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'product_image_id');
    }
}
