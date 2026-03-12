<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoyaltyProgramRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->requiredIfPost(),'string','max:255'],
            'description' => ['sometimes','nullable','string','max:500'],
            'min_appointment_amount' => [$this->requiredIfPost(),'integer','min:0'],
            'is_active' => [$this->requiredIfPost(), 'boolean'],
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
