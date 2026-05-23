<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo_path',
        'logo_disk',
        'banner_path',
        'banner_disk',
        'description',
        'contact_email',
        'contact_phone',
        'telegram_url',
        'messenger_url',
        'country_code',
        'state',
        'city',
        'address_line',
        'latitude',
        'longitude',
        'status',
        'is_verified',
        'followers_count',
        'published_at',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path || ! $this->logo_disk) {
            return null;
        }

        return Storage::disk($this->logo_disk)->url($this->logo_path);
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (! $this->banner_path || ! $this->banner_disk) {
            return null;
        }

        return Storage::disk($this->banner_disk)->url($this->banner_path);
    }

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_followers')->withTimestamps();
    }
}
