<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'sku',
        'description',
        'price',
        'price_amount',
        'currency',
        'image',
        'location',
        'location_country_code',
        'location_state',
        'location_city',
        'stock_quantity',
        'status',
        'moderation_status',
        'visibility',
        'allow_offers',
        'published_at',
        'user_id',
        'store_id',
        'category_id',
        'product_condition_id',
        'schedule_at',
        'auto_post',
    ];

    protected function casts(): array
    {
        return [
            'allow_offers' => 'boolean',
            'published_at' => 'datetime',
            'schedule_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class, 'product_condition_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}
