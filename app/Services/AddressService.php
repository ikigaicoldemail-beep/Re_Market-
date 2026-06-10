<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function list(User $user): Collection
    {
        return $user->addresses()->latest()->get();
    }

    public function create(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            if (($data['is_default'] ?? false) === true) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                ...$data,
                'country_code' => strtoupper((string) $data['country_code']),
                'type' => $data['type'] ?? 'shipping',
                'is_default' => $data['is_default'] ?? false,
            ]);
        });
    }

    public function update(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            if (($data['is_default'] ?? false) === true) {
                $address->user->addresses()->update(['is_default' => false]);
            }

            $address->fill([
                ...$data,
                'country_code' => array_key_exists('country_code', $data)
                    ? strtoupper((string) $data['country_code'])
                    : $address->country_code,
            ]);
            $address->save();

            return $address->fresh();
        });
    }

    public function delete(Address $address): void
    {
        $address->delete();
    }
}
