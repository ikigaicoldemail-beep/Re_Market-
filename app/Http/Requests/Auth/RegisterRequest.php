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
            'admin_key' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('role') !== 'admin') {
                return;
            }

            $configuredKey = config('auth.admin_registration_key');
            $submittedKey = (string) $this->input('admin_key', '');

            if (! is_string($configuredKey) || $configuredKey === '' || ! hash_equals($configuredKey, $submittedKey)) {
                $validator->errors()->add('admin_key', 'A valid admin registration key is required.');
            }
        });
    }
}
