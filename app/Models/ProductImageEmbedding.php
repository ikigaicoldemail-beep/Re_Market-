<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImageEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_image_id',
        'provider',
        'embedding_model',
        'embedding_vector',
        'vector_hash',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding_vector' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function productImage(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class);
    }
}
