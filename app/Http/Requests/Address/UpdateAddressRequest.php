<?php

namespace App\Http\Requests\Address;

class UpdateAddressRequest extends StoreAddressRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach ($rules as $field => $rule) {
            array_unshift($rules[$field], 'sometimes');
        }

        return $rules;
    }
}
