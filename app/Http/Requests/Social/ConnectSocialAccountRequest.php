<?php

namespace App\Http\Requests\Social;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConnectSocialAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', Rule::in(['facebook', 'tiktok'])],
            'provider_user_id' => ['required', 'string', 'max:255'],
            'provider_account_name' => ['nullable', 'string', 'max:255'],
            'access_token' => ['required', 'string'],
            'refresh_token' => ['nullable', 'string'],
            'token_expires_at' => ['nullable', 'date'],
            'scopes' => ['nullable', 'array'],
        ];
    }
}
