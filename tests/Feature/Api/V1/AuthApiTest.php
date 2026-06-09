<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'profile'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'user',
        ]);
    }

    public function test_user_cannot_register_as_admin_without_admin_key(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567891',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('admin_key');

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@example.com',
        ]);
    }

    public function test_user_can_register_as_admin_with_valid_admin_key(): void
    {
        config(['auth.admin_registration_key' => 'secret-admin-key']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
            'admin_key' => 'secret-admin-key',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', 'admin');

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_registration_requires_a_symbol_in_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_login_rejects_overly_long_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => str_repeat('a', 256).'@example.com',
            'password' => str_repeat('x', 256),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
