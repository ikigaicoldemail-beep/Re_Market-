<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PromoBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'image_disk',
        'link_url',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path || ! $this->image_disk) {
            return null;
        }

        return Storage::disk($this->image_disk)->url($this->image_path);
    }
}
