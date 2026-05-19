<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReport extends Model
{
    use HasFactory;

    public const REASONS = [
        'prohibited' => 'Prohibited item',
        'counterfeit' => 'Counterfeit or fake',
        'misleading' => 'Misleading description or photos',
        'spam' => 'Spam or duplicate listing',
        'offensive' => 'Offensive or inappropriate content',
        'scam' => 'Looks like a scam',
        'other' => 'Other',
    ];

    public const STATUSES = ['open', 'resolved', 'dismissed'];

    protected $fillable = [
        'product_id',
        'reporter_id',
        'reason',
        'details',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_note',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
