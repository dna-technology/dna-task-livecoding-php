<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO authenticated user needs to match merchant id
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'merchantId' => ['uuid'],
            'from'       => [['required'], ['date_format', 'Y-m-d H:i:s']],
            'to'         => [['required'], ['date_format', 'Y-m-d H:i:s'], ['after', 'from']],
        ];
    }
}