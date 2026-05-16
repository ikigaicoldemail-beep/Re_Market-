<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

    public function uploadAvatar(User $user, UploadedFile $image): User
    {
        if (! str_starts_with((string) $image->getMimeType(), 'image/')) {
            throw ValidationException::withMessages([
                'image' => ['Only valid image uploads are allowed.'],
            ]);
        }

        DB::transaction(function () use ($user, $image) {
            $profile = $user->profile()->firstOrCreate([], [
                'profile_visibility' => 'public',
                'is_seller' => false,
            ]);

            // Delete old avatar if exists
            if ($profile->avatar_path && Storage::disk('profile-images')->exists($profile->avatar_path)) {
                Storage::disk('profile-images')->delete($profile->avatar_path);
            }

            // Store new avatar
            $path = $image->store('avatars', 'profile-images');
            $profile->avatar_path = $path;
            $profile->save();
        });

        return $user->fresh(['profile.defaultStore', 'stores']);
    }

    public function uploadCover(User $user, UploadedFile $image): User
    {
        if (! str_starts_with((string) $image->getMimeType(), 'image/')) {
            throw ValidationException::withMessages([
                'image' => ['Only valid image uploads are allowed.'],
            ]);
        }

        DB::transaction(function () use ($user, $image) {
            $profile = $user->profile()->firstOrCreate([], [
                'profile_visibility' => 'public',
                'is_seller' => false,
            ]);

            // Delete old cover if exists
            if ($profile->cover_path && Storage::disk('profile-images')->exists($profile->cover_path)) {
                Storage::disk('profile-images')->delete($profile->cover_path);
            }

            // Store new cover
            $path = $image->store('covers', 'profile-images');
            $profile->cover_path = $path;
            $profile->save();
        });

        return $user->fresh(['profile.defaultStore', 'stores']);
    }
}
