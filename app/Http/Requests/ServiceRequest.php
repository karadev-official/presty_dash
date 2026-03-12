<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return array_merge([
            'name' => [$this->requiredIfPost(), 'string', 'max:255'],
            'slug' => [$this->requiredIfPost(), 'string', 'max:255'],
            'service_category_id' => [$this->requiredIfPost(), 'exists:service_categories,id'],
            'description' => ['nullable', 'string', 'max:600'],
            'duration' => ['required', 'integer', 'min:0'],
            'price' => [$this->requiredIfPost(), 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'image_ids' => ['nullable', 'array'],
            'option_groups' => ['sometimes', 'array'],
        ], $this->optionGroupRules(), $this->optionRules());
    }

    protected function optionGroupRules(): array
    {
        return [
            'option_groups.*.id' => ['nullable', 'integer'],
            'option_groups.*.client_id' => ['sometimes', 'string', 'max:255'],

            'option_groups.*.name' => ['required_with:option_groups', 'string', 'max:255'],
            'option_groups.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.selection_type' => ['required_with:option_groups', Rule::in(['single', 'multiple'])],
            'option_groups.*.is_required' => ['required_with:option_groups', 'boolean'],
            'option_groups.*.min_select' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.max_select' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'option_groups.*.position' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options' => ['sometimes', 'array'],
        ];
    }

    protected function optionRules(): array
    {
        return [
            'option_groups.*.options.*.id' => ['nullable', 'integer'],
            'option_groups.*.options.*.client_id' => ['sometimes', 'string', 'max:255'],

            'option_groups.*.options.*.name' => ['required_with:option_groups.*.options', 'string', 'max:255'],
            'option_groups.*.options.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.options.*.duration' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.price' => ['required_with:option_groups.*.options', 'integer', 'min:0'],
            'option_groups.*.options.*.position' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.image_id' => ['sometimes', 'nullable', 'integer', 'exists:images,id'],
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
