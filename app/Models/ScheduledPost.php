<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_post_id',
        'scheduled_for',
        'status',
        'attempts',
        'last_attempt_at',
        'processed_at',
        'cancelled_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'last_attempt_at' => 'datetime',
            'processed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }
}
