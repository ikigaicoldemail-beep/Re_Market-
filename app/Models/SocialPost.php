<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SocialPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'social_account_id',
        'platform',
        'caption',
        'media_payload',
        'status',
        'provider_post_id',
        'provider_response',
        'error_message',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'media_payload' => 'array',
            'provider_response' => 'array',
            'posted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function scheduledPost(): HasOne
    {
        return $this->hasOne(ScheduledPost::class);
    }
}
