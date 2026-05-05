<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $role = $data['role'] ?? 'user';

        unset($data['role'], $data['admin_key']);

        return $this->registerWithRole($data, $role);
    }

    private function registerWithRole(array $data, string $role): array
    {
        $user = DB::transaction(function () use ($data, $role) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'status' => 'active',
                'role' => $role,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'profile_visibility' => 'public',
                'is_seller' => false,
            ]);

            return $user;
        });

        $token = Auth::guard('api')->login($user);
        $user->load(['profile.defaultStore', 'stores']);

        return [$user, $token];
    }

    public function login(array $credentials): array
    {
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();

        if ($user->status !== 'active') {
            Auth::guard('api')->logout();

            throw ValidationException::withMessages([
                'email' => ['This account is not active.'],
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $user->load(['profile.defaultStore', 'stores']);

        return [$user, $token];
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function (User $user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}
