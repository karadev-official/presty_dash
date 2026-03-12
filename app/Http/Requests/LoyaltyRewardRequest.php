<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoyaltyRewardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'required_visits' => [$this->requiredIfPost(), 'integer', 'min:0'],
            'discount_amount' => [$this->requiredIfPost(), 'integer', 'min:0'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
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
