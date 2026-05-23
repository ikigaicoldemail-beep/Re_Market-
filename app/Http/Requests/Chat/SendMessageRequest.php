<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', Rule::in(['text', 'image', 'system'])],
            'attachment' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $this->filled('body') && ! $this->hasFile('attachment')) {
                    $validator->errors()->add('body', 'A message body or attachment is required.');
                }
            },
        ];
    }
}
