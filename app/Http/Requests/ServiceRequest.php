<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function rules(): array
    {
        if(request()->isMethod('POST')){
            return [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255'],
                'service_category_id' => ['required', 'exists:service_categories,id'],
                'description' => ['nullable', 'string', 'max:600'],
                'duration' => ['required', 'integer', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                'is_active' => ['sometimes', 'boolean'],
                'is_online' => ['sometimes', 'boolean'],
                'image_ids' => ['sometimes', 'array'],
                'option_groups' => ['sometimes', 'array'],

                'option_groups.*.id' => ['nullable', 'integer'], // pas de exists ici si tu veux être souple
                'option_groups.*.client_id' => ['sometimes', 'string', 'max:255'], // interface only (ignored)
                'option_groups.*.name' => ['required_with:option_groups', 'string', 'max:255'],
                'option_groups.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
                'option_groups.*.selection_type' => ['required_with:option_groups', Rule::in(['single', 'multiple'])],
                'option_groups.*.is_required' => ['required_with:option_groups', 'boolean'],
                'option_groups.*.min_select' => ['sometimes', 'integer', 'min:0'],
                'option_groups.*.max_select' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'option_groups.*.position' => ['sometimes', 'integer', 'min:0'],

                'option_groups.*.options' => ['sometimes', 'array'],
                'option_groups.*.options.*.id' => ['nullable', 'integer'],
                'option_groups.*.options.*.client_id' => ['sometimes', 'string', 'max:255'], // ignored
                'option_groups.*.options.*.name' => ['required_with:option_groups.*.options', 'string', 'max:255'],
                'option_groups.*.options.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
                'option_groups.*.options.*.duration' => ['sometimes', 'integer', 'min:0'],
                'option_groups.*.options.*.price' => ['required_with:option_groups.*.options', 'integer', 'min:0'], // centimes
                'option_groups.*.options.*.position' => ['sometimes', 'integer', 'min:0'],
                'option_groups.*.options.*.image_id' => ['sometimes', 'nullable', 'integer', 'exists:images,id'],
            ];
        }
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'service_category_id' => ['sometimes', 'exists:service_categories,id'],
            'description' => ['nullable', 'string', 'max:600'],
            'duration' => ['required', 'integer', 'min:0'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'image_ids' => ['nullable', 'array'],
            'option_groups' => ['sometimes', 'array'],

            'option_groups.*.id' => ['nullable', 'integer'], // pas de exists ici si tu veux être souple
            'option_groups.*.client_id' => ['sometimes', 'string', 'max:255'], // interface only (ignored)
            'option_groups.*.name' => ['required_with:option_groups', 'string', 'max:255'],
            'option_groups.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.selection_type' => ['required_with:option_groups', Rule::in(['single', 'multiple'])],
            'option_groups.*.is_required' => ['required_with:option_groups', 'boolean'],
            'option_groups.*.min_select' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.max_select' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'option_groups.*.position' => ['sometimes', 'integer', 'min:0'],

            'option_groups.*.options' => ['sometimes', 'array'],
            'option_groups.*.options.*.id' => ['nullable', 'integer'],
            'option_groups.*.options.*.client_id' => ['sometimes', 'string', 'max:255'], // ignored
            'option_groups.*.options.*.name' => ['required_with:option_groups.*.options', 'string', 'max:255'],
            'option_groups.*.options.*.slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'option_groups.*.options.*.duration' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.price' => ['required_with:option_groups.*.options', 'integer', 'min:0'], // centimes
            'option_groups.*.options.*.position' => ['sometimes', 'integer', 'min:0'],
            'option_groups.*.options.*.image_id' => ['sometimes', 'nullable', 'integer', 'exists:images,id'],
        ];

    }

    public function authorize(): bool
    {
        return true;
    }
}
