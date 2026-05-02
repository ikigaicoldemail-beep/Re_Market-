<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'avatar_path',
        'cover_path',
        'bio',
        'gender',
        'date_of_birth',
        'country_code',
        'city',
        'state',
        'default_store_id',
        'is_seller',
        'profile_visibility',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_seller' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'default_store_id');
    }
}
