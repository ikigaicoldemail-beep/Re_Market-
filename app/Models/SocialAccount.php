<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'provider_user_id',
        'provider_account_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'status',
        'last_synced_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $this->encryptTokenForStorage($value);
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $this->encryptTokenForStorage($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    private function encryptTokenForStorage(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            Crypt::decryptString($value);

            return $value;
        } catch (\Throwable) {
            return Crypt::encryptString($value);
        }
    }
}
