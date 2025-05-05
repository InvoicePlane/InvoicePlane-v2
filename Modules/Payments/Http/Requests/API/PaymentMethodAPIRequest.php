<?php

namespace Modules\Payments\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Http\Requests\API\APIRequest;

class PaymentMethodAPIRequest extends APIRequest
{
    protected $stopOnFirstFailure = true;

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
            'payment_method_name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
         * #24: Since we're dealing with legacy database fields
         * the `product_description` field needs to become an empty string ''
         * when null is passed
         */
        /*$this->merge([
            'product_description' => $this->input('product_description') ?? ''
        ]);*/
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => trans('ip_validation.given_data_invalid'),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
