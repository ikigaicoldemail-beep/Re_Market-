<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'logo_path',
        'logo_disk',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
