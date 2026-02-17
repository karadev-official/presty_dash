<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function rules(): array
    {
        if(request()->isMethod("POST")) {
            return [
                'street' => ['required'],
                'city' => ['required'],
                'postal_code' => ['required'],
                'country' => ['required'],
                'additional_info' => ['nullable'],
                'lat' => ['nullable', 'numeric'],
                'lng' => ['nullable', 'numeric'],
            ];
        }
        return [
            'street' => ['sometimes','required'],
            'city' => ['sometimes','required'],
            'postal_code' => ['sometimes','required'],
            'country' => ['sometimes','required'],
            'additional_info' => ['sometimes','nullable'],
            'lat' => ['sometimes','nullable', 'numeric'],
            'lng' => ['sometimes','nullable', 'numeric'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
