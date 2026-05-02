<?php

namespace App\Http\Requests\Social;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleSocialPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'social_post_id' => ['required', 'integer', 'exists:social_posts,id'],
            'scheduled_for' => ['required', 'date', 'after:now'],
        ];
    }
}
