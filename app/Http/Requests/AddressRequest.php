<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'street' => [$this->requiredIfPost(), 'string'],
            'city' => [$this->requiredIfPost(), 'string'],
            'postal_code' => [$this->requiredIfPost(), 'string'],
            'country' => [$this->requiredIfPost(), 'string'],
            'additional_info' => ['sometimes', 'nullable', 'string'],
            'lat' => ['sometimes','nullable', 'numeric'],
            'lng' => ['sometimes','nullable', 'numeric'],
        ];
    }

    protected function requiredIfPost(): string
    {
        return $this->isMethod('POST') ? 'required' : 'sometimes';
    }

    public function authorize(): bool
    {
        return true;
    }
}
