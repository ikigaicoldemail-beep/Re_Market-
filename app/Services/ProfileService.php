<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function update(User $user, array $data): User
    {
        DB::transaction(function () use ($user, $data) {
            $user->fill([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone' => array_key_exists('phone', $data) ? $data['phone'] : $user->phone,
            ]);
            $user->save();

            $profile = $user->profile()->firstOrCreate([], [
                'profile_visibility' => 'public',
                'is_seller' => false,
            ]);

            $profile->fill([
                'username' => $data['username'] ?? $profile->username,
                'bio' => array_key_exists('bio', $data) ? $data['bio'] : $profile->bio,
                'gender' => array_key_exists('gender', $data) ? $data['gender'] : $profile->gender,
                'date_of_birth' => array_key_exists('date_of_birth', $data) ? $data['date_of_birth'] : $profile->date_of_birth,
                'country_code' => array_key_exists('country_code', $data) ? strtoupper((string) $data['country_code']) : $profile->country_code,
                'state' => array_key_exists('state', $data) ? $data['state'] : $profile->state,
                'city' => array_key_exists('city', $data) ? $data['city'] : $profile->city,
                'profile_visibility' => $data['profile_visibility'] ?? $profile->profile_visibility,
            ]);
            $profile->save();
        });

        return $user->fresh(['profile.defaultStore', 'stores']);
    }
}
