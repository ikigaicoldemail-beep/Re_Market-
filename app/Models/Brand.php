<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'logo_disk',
        'description',
        'status',
        'sort_order',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path || ! $this->logo_disk) {
            return null;
        }

        $url = Storage::disk($this->logo_disk)->url($this->logo_path);

        if (config('filesystems.disks.'.$this->logo_disk.'.driver') === 'local') {
            return parse_url($url, PHP_URL_PATH) ?: $url;
        }

        return $url;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
