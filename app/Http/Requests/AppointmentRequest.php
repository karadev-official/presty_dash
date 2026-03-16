<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PhpParser\Node\Expr\Array_;

class AppointmentRequest extends FormRequest
{
    public function rules(): array
    {
//        if(request()->isMethod('POST')) {
//            return [
//                'cancelled_at' => ['nullable', 'date'],
//                'cancellation_reason' => ['nullable'],
//                'cancelled_by' => ['nullable', 'exists:users'],
//                'reminder_sent' => ['boolean'],
//                'reminder_sent_at' => ['nullable', 'date'],
//                'confirmed_at' => ['nullable', 'date'],
//                'completed_at' => ['nullable', 'date'],
//
//                'appointment_services' => ['sometimes', 'array'],
//                'appointment_services.*.service_id' => ['required', 'integer', 'exists:services,id'],
//                'appointment_services.*.price' => ['required', 'integer', 'min:0'],
//                'appointment_services.*.name' => ['required', 'string'],
//                'appointment_services.*.duration' => ['required', 'integer', 'min:0'],
//                'appointment_services.*.quantity' => ['required', 'integer', 'min:1'],
//                'appointment_services.*.total' => ['required', 'integer', 'min:0'],
//                'appointment_services.*.notes' => ['nullable', 'string'],
//                'appointment_services.*.options' => ['sometimes', 'array'],
//
//                'appointment_services.*.options.*.service_option_id' => ['required', 'integer', 'exists:service_options,id'],
//                'appointment_services.*.options.*.service_option_group_id' => ['required','integer', 'exists:service_option_groups,id'],
//                'appointment_services.*.options.*.option_name' => ['required', 'string'],
//                'appointment_services.*.options.*.group_name' => ['required', 'string'],
//                'appointment_services.*.options.*.price' => ['required', 'integer', 'min:0'],
//                'appointment_services.*.options.*.duration' => ['required', 'integer', 'min:0'],
//
//                'appointment_products' => ['sometimes', 'array'],
//                'appointment_products.*.product_id' => ['required', 'integer', 'exists:products,id'],
//                'appointment_products.*.name' => ['required', 'string'],
//                'appointment_products.*.price' => ['required', 'integer', 'min:0'],
//                'appointment_products.*.quantity' => ['required', 'integer', 'min:1'],
//                'appointment_products.*.total' => ['required', 'integer', 'min:0'],
//                'appointment_products.*.notes' => ['nullable', 'string'],
//            ];
//        }
        return array_merge([
                'professional_profile_id' => [$this->requiredIfPost(), 'exists:professional_profiles,id'],
                'customer_id' => [$this->requiredIfPost(), 'exists:customers,id'],
                'resource_id' => ['sometimes','nullable', 'exists:resources,id'],
                'workplace_id' => ['sometimes','nullable', 'exists:workplaces,id'],
                'date' => [$this->requiredIfPost(), 'date'],
                'start_time' => [$this->requiredIfPost(), 'date_format:H:i'],
                'end_time' => ['sometimes', 'nullable', 'date_format:H:i'],
                'duration' => [$this->requiredIfPost(), 'integer'],
                'status' => [$this->requiredIfPost(), 'string'],
                'subtotal' => [$this->requiredIfPost(), 'integer'],
                'discount' => [$this->requiredIfPost(), 'integer'],
                'total' => [$this->requiredIfPost(), 'integer'],
                'customer_notes' => ['sometimes','nullable'],
                'internal_notes' => ['sometimes','nullable'],

                'cancelled_at' => ['sometimes','nullable', 'date'],
                'cancellation_reason' => ['sometimes','nullable', 's'],
                'cancelled_by' => ['sometimes','nullable', 'exists:users,id'],
                'reminder_sent' => ['sometimes','boolean'],
                'reminder_sent_at' => ['sometimes','nullable', 'date'],
                'confirmed_at' => ['sometimes','nullable', 'date'],
                'completed_at' => ['sometimes','nullable', 'date'],

                'appointment_services' => ['sometimes', 'array'],
                'appointment_products' => ['sometimes', 'array'],
                'payments' => ['sometimes', 'array'],
            ],
            $this->appointmentServicesRules(),
            $this->appointmentProductRules(),
            $this->paymentRules()
        );
    }

    protected function requiredIfPost(): string
    {
        return $this->isMethod('POST') ? 'required' : 'sometimes';
    }

    protected function paymentRules(): array
    {
        return [
            'payments.*.id' => ['sometimes', 'integer', 'exists:appointment_payments,id'],
            'payments.*.payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'payments.*.amount' => ['required', 'integer', 'min:0'],
            'payments.*.notes' => ['nullable', 'string', 'max:500'],
            'payments.*.is_deposit' => ['sometimes','boolean'],
        ];
    }

    protected function appointmentProductRules(): array
    {
        return [
            'appointment_products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'appointment_products.*.price' => ['required', 'integer', 'min:0'],
            'appointment_products.*.name' => ['required', 'string'],
            'appointment_products.*.quantity' => ['required', 'integer', 'min:1'],
            'appointment_products.*.total' => ['required', 'integer', 'min:0'],
            'appointment_products.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function appointmentServicesRules(): array
    {
        return [
            'appointment_services.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'appointment_services.*.price' => ['required', 'integer', 'min:0'],
            'appointment_services.*.name' => ['required', 'string'],
            'appointment_services.*.duration' => ['required', 'integer', 'min:0'],
            'appointment_services.*.quantity' => ['required', 'integer', 'min:1'],
            'appointment_services.*.total' => ['required', 'integer', 'min:0'],
            'appointment_services.*.notes' => ['nullable', 'string'],
            'appointment_services.*.options' => ['sometimes', 'array'],

            'appointment_services.*.options.*.service_option_id' => ['required', 'integer', 'exists:service_options,id'],
            'appointment_services.*.options.*.service_option_group_id' => ['required','integer', 'exists:service_option_groups,id'],
            'appointment_services.*.options.*.option_name' => ['required', 'string'],
            'appointment_services.*.options.*.group_name' => ['required', 'string'],
            'appointment_services.*.options.*.price' => ['required', 'integer', 'min:0'],
            'appointment_services.*.options.*.duration' => ['required', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
