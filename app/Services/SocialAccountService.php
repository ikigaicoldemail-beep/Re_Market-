<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SocialAccountService
{
    public function list(User $user): Collection
    {
        return $user->socialAccounts()->latest()->get();
    }

    public function connect(User $user, array $data): SocialAccount
    {
        return DB::transaction(function () use ($user, $data) {
            return SocialAccount::updateOrCreate(
                [
                    'platform' => $data['platform'],
                    'provider_user_id' => $data['provider_user_id'],
                ],
                [
                    'user_id' => $user->id,
                    'provider_account_name' => $data['provider_account_name'] ?? null,
                    'access_token' => Crypt::encryptString($data['access_token']),
                    'refresh_token' => isset($data['refresh_token']) ? Crypt::encryptString($data['refresh_token']) : null,
                    'token_expires_at' => $data['token_expires_at'] ?? null,
                    'scopes' => $data['scopes'] ?? [],
                    'status' => 'active',
                    'last_synced_at' => now(),
                ]
            );
        });
    }

    public function disconnect(SocialAccount $account): SocialAccount
    {
        $account->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
        ]);

        return $account->fresh();
    }
}
