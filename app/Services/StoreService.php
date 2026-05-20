<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreService
{
    public function create(User $user, array $data): Store
    {
        return DB::transaction(function () use ($user, $data) {
            $payload = [
                'name' => $data['name'],
                'slug' => $this->resolveUniqueSlug($data['slug'] ?? $data['name']),
                'description' => $data['description'] ?? null,
                'contact_email' => $data['contact_email'] ?? $user->email,
                'contact_phone' => $data['contact_phone'] ?? $user->phone,
                'telegram_url' => $data['telegram_url'] ?? null,
                'messenger_url' => $data['messenger_url'] ?? null,
                'country_code' => isset($data['country_code']) ? strtoupper((string) $data['country_code']) : null,
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'address_line' => $data['address_line'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => $data['status'] ?? 'active',
                'published_at' => now(),
            ];

            if (($data['logo'] ?? null) instanceof UploadedFile) {
                $payload['logo_path'] = $data['logo']->store('', 'store-images');
                $payload['logo_disk'] = 'store-images';
            }

            if (($data['banner'] ?? null) instanceof UploadedFile) {
                $payload['banner_path'] = $data['banner']->store('', 'store-images');
                $payload['banner_disk'] = 'store-images';
            }

            $store = $user->stores()->create($payload);

            $profile = $user->profile()->firstOrCreate([], [
                'profile_visibility' => 'public',
                'is_seller' => true,
            ]);

            $profile->is_seller = true;
            $profile->default_store_id ??= $store->id;
            $profile->save();

            if ($user->role === 'user') {
                $user->role = 'seller';
                $user->save();
            }

            return $store->fresh();
        });
    }

    public function update(Store $store, array $data): Store
    {
        $store->fill([
            'name' => $data['name'] ?? $store->name,
            'slug' => isset($data['slug']) ? $this->resolveUniqueSlug($data['slug'], $store->id) : $store->slug,
            'description' => array_key_exists('description', $data) ? $data['description'] : $store->description,
            'contact_email' => array_key_exists('contact_email', $data) ? $data['contact_email'] : $store->contact_email,
            'contact_phone' => array_key_exists('contact_phone', $data) ? $data['contact_phone'] : $store->contact_phone,
            'telegram_url' => array_key_exists('telegram_url', $data) ? $data['telegram_url'] : $store->telegram_url,
            'messenger_url' => array_key_exists('messenger_url', $data) ? $data['messenger_url'] : $store->messenger_url,
            'country_code' => array_key_exists('country_code', $data) ? strtoupper((string) $data['country_code']) : $store->country_code,
            'state' => array_key_exists('state', $data) ? $data['state'] : $store->state,
            'city' => array_key_exists('city', $data) ? $data['city'] : $store->city,
            'address_line' => array_key_exists('address_line', $data) ? $data['address_line'] : $store->address_line,
            'latitude' => array_key_exists('latitude', $data) ? $data['latitude'] : $store->latitude,
            'longitude' => array_key_exists('longitude', $data) ? $data['longitude'] : $store->longitude,
            'status' => $data['status'] ?? $store->status,
        ]);

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            if ($store->logo_path && $store->logo_disk) {
                Storage::disk($store->logo_disk)->delete($store->logo_path);
            }
            $store->logo_path = $data['logo']->store('', 'store-images');
            $store->logo_disk = 'store-images';
        } elseif (! empty($data['remove_logo']) && $store->logo_path && $store->logo_disk) {
            Storage::disk($store->logo_disk)->delete($store->logo_path);
            $store->logo_path = null;
            $store->logo_disk = null;
        }

        if (($data['banner'] ?? null) instanceof UploadedFile) {
            if ($store->banner_path && $store->banner_disk) {
                Storage::disk($store->banner_disk)->delete($store->banner_path);
            }
            $store->banner_path = $data['banner']->store('', 'store-images');
            $store->banner_disk = 'store-images';
        } elseif (! empty($data['remove_banner']) && $store->banner_path && $store->banner_disk) {
            Storage::disk($store->banner_disk)->delete($store->banner_path);
            $store->banner_path = null;
            $store->banner_disk = null;
        }

        $store->save();

        return $store->fresh();
    }

    private function resolveUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'store';
        $slug = $base;
        $counter = 1;

        while (
            Store::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
