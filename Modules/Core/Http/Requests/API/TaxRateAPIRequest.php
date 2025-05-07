<?php

namespace Modules\Core\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaxRateAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        return $this->all();
    }

    public function rules(): array
    {
        return [
            // Other Required fields
            'tax_rate_name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
            'tax_rate_percent' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'numeric',
            ],
        ];
    }

    protected function prepareForValidation(): void {}

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => trans('ip_validation.given_data_invalid'),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
