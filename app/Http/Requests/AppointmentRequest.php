<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        if(request()->isMethod('POST')) {
            return [
                'professional_profile_id' => ['required', 'exists:professional_profiles,id'],
                'customer_id' => ['required', 'exists:customers,id'],
                'resource_id' => ['nullable', 'exists:resources,id'],
                'date' => ['required', 'date'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i'],
                'duration' => ['required', 'integer'],
                'status' => ['required'],
                'subtotal' => ['required', 'integer'],
                'discount' => ['required', 'integer'],
                'total' => ['required', 'integer'],
                'customer_notes' => ['nullable'],
                'internal_notes' => ['nullable'],
                'payment_status' => ['required'],
                'deposit_amount' => ['required', 'integer'],
                'deposit_paid_at' => ['nullable', 'date'],
                'deposit_payment_method' => ['nullable'],
                'amount_paid' => ['required', 'integer'],
                'remaining_amount' => ['required', 'integer'],
                'paid_at' => ['nullable', 'date'],
                'payment_method' => ['required'],
                'cancelled_at' => ['nullable', 'date'],
                'cancellation_reason' => ['nullable'],
                'cancelled_by' => ['nullable', 'exists:users'],
                'reminder_sent' => ['boolean'],
                'reminder_sent_at' => ['nullable', 'date'],
                'confirmed_at' => ['nullable', 'date'],
                'completed_at' => ['nullable', 'date'],

                'services' => ['sometimes', 'array'],
                'services.*.service_id' => ['required', 'integer', 'exists:services,id'],
                'services.*.price' => ['required', 'integer', 'min:0'],
                'services.*.duration' => ['required', 'integer', 'min:0'],
                'services.*.quantity' => ['required', 'integer', 'min:1'],
                'services.*.total' => ['required', 'integer', 'min:0'],
                'services.*.notes' => ['nullable', 'string'],
                'services.*.options' => ['sometimes', 'array'],

                'services.*.options.*.service_option_id' => ['required', 'integer', 'exists:service_options,id'],
                'services.*.options.*.service_option_group_id' => ['required','integer', 'exists:service_option_groups,id'],
                'services.*.options.*.option_name' => ['required', 'string'],
                'services.*.options.*.group_name' => ['required', 'string'],
                'services.*.options.*.price' => ['required', 'integer', 'min:0'],
                'services.*.options.*.duration' => ['required', 'integer', 'min:0'],

                'products' => ['sometimes', 'array'],
                'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
                'products.*.price' => ['required', 'integer', 'min:0'],
                'products.*.quantity' => ['required', 'integer', 'min:1'],
                'products.*.total' => ['required', 'integer', 'min:0'],
                'products.*.notes' => ['nullable', 'string'],
            ];
        }
        return [
            'customer_id' => ['sometimes','required', 'exists:customers'],
            'resource_id' => ['sometimes','nullable', 'exists:resources'],
            'date' => ['sometimes','required', 'date'],
            'start_time' => ['sometimes','required', 'date'],
            'end_time' => ['sometimes','required', 'date'],
            'duration' => ['sometimes','required', 'integer'],
            'status' => ['sometimes','required'],
            'subtotal' => ['sometimes','required', 'integer'],
            'discount' => ['sometimes','required', 'integer'],
            'total' => ['sometimes','required', 'integer'],
            'customer_notes' => ['sometimes','nullable'],
            'internal_notes' => ['sometimes','nullable'],
            'payment_status' => ['sometimes','required'],
            'deposit_amount' => ['sometimes','required', 'integer'],
            'deposit_paid_at' => ['sometimes','nullable', 'date'],
            'deposit_payment_method' => ['sometimes','nullable'],
            'amount_paid' => ['sometimes','required', 'integer'],
            'remaining_amount' => ['sometimes','required', 'integer'],
            'paid_at' => ['sometimes','nullable', 'date'],
            'payment_method' => ['sometimes','required'],
            'cancelled_at' => ['sometimes','nullable', 'date'],
            'cancellation_reason' => ['sometimes','nullable'],
            'cancelled_by' => ['sometimes','nullable', 'exists:users'],
            'reminder_sent' => ['sometimes','boolean'],
            'reminder_sent_at' => ['sometimes','nullable', 'date'],
            'confirmed_at' => ['sometimes','nullable', 'date'],
            'completed_at' => ['sometimes','nullable', 'date'],

            'services' => ['sometimes', 'array'],
            'services.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'services.*.price' => ['required', 'integer', 'min:0'],
            'services.*.duration' => ['required', 'integer', 'min:0'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
            'services.*.total' => ['required', 'integer', 'min:0'],
            'services.*.notes' => ['nullable', 'string'],
            'services.*.options' => ['sometimes', 'array'],

            'services.*.options.*.service_option_id' => ['required', 'integer', 'exists:service_options,id'],
            'services.*.options.*.service_option_group_id' => ['required','integer', 'exists:service_option_groups,id'],
            'services.*.options.*.option_name' => ['required', 'string'],
            'services.*.options.*.group_name' => ['required', 'string'],
            'services.*.options.*.price' => ['required', 'integer', 'min:0'],
            'services.*.options.*.duration' => ['required', 'integer', 'min:0'],

            'products' => ['sometimes', 'array'],
            'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'products.*.price' => ['required', 'integer', 'min:0'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.total' => ['required', 'integer', 'min:0'],
            'products.*.notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
