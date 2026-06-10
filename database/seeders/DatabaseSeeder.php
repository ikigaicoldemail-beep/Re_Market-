<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductConditionSeeder::class,
            CambodiaMarketplaceSeeder::class,
        ]);

        // Test account (for local development / PHPUnit)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'              => 'Test User',
                'password'          => Hash::make('password'),
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $ppm = User::updateOrCreate(
            ['email' => 'ppm@gmail.com'],
            [
                'name'              => 'PPM',
                'password'          => Hash::make('password'),
                'role'              => 'seller',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        UserProfile::firstOrCreate(
            ['user_id' => $ppm->id],
            [
                'username' => Str::slug($ppm->name).'-'.$ppm->id,
                'is_seller' => true,
                'profile_visibility' => 'public',
                'country_code' => 'KH',
            ]
        );

        $hour = User::updateOrCreate(
            ['email' => 'houradmin@gmail.com'],
            [
                'name'              => 'hour',
                'password'          => Hash::make('hour1234'),
                'role'              => 'admin',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        UserProfile::firstOrCreate(
            ['user_id' => $hour->id],
            [
                'username' => Str::slug($hour->name).'-'.$hour->id,
                'is_seller' => true,
                'profile_visibility' => 'public',
                'country_code' => 'KH',
            ]
        );
    }
}
