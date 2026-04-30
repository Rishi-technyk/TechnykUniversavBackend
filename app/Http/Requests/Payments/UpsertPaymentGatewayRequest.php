<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gatewayId = $this->route('gateway');
        $gatewayKey = is_object($gatewayId) ? $gatewayId->id : $gatewayId;

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', Rule::unique('payment_gateways', 'slug')->ignore($gatewayKey)],
            'environment' => ['required', Rule::in(['test', 'live'])],
            'is_active' => ['nullable', 'boolean'],
            'merchant_id' => ['nullable', 'string'],
            'key_id' => ['nullable', 'string'],
            'key_secret' => ['nullable', 'string'],
            'salt' => ['nullable', 'string'],
            'webhook_secret' => ['nullable', 'string'],
            'app_id' => ['nullable', 'string'],
            'encryption_key' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
