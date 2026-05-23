<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['nullable', 'string', 'in:user,admin'],
            'admin_key' => [
                'required_if:role,admin',
                function ($attribute, $value, $fail) {
                    if ($this->input('role') !== 'admin') {
                        return;
                    }
                    $expected = config('auth.admin_registration_key');
                    if (! $expected || $value !== $expected) {
                        $fail('The admin key is incorrect.');
                    }
                },
            ],
        ];
    }
}
