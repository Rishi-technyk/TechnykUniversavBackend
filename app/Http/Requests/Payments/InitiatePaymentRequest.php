<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'module' => ['required', 'string'],
            'module_reference_id' => ['nullable'],
            'type' => ['nullable', 'string'],
        ];
    }
}
