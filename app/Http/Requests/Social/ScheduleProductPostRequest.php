<?php

namespace App\Http\Requests\Social;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleProductPostRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->route('product')) {
            $this->merge(['product_id' => $this->route('product')->id]);
        }

        if ($this->has('Id_product') && ! $this->has('product_id')) {
            $this->merge(['product_id' => $this->input('Id_product')]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'scheduled_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'post_to' => ['required', 'in:marketplace,user_account'],
            'social_account_id' => ['required_if:post_to,user_account', 'exists:social_accounts,id'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'scheduled_date.required' => 'Scheduled date is required.',
            'scheduled_date.date' => 'Scheduled date must be a valid date.',
            'scheduled_date.after_or_equal' => 'Scheduled date must be today or in the future.',
            'scheduled_time.required' => 'Scheduled time is required.',
            'scheduled_time.date_format' => 'Scheduled time must be in HH:MM format.',
            'post_to.required' => 'Post destination is required.',
            'post_to.in' => 'Post destination must be either "marketplace" or "user_account".',
            'social_account_id.required_if' => 'Social account is required when posting to user account.',
            'social_account_id.exists' => 'The selected social account does not exist.',
        ];
    }
}
