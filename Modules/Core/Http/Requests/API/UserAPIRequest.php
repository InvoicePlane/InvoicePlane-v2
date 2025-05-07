<?php

namespace Modules\Core\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Models\User;

class UserAPIRequest extends APIRequest
{
    protected $stopOnFirstFailure = false;

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
            'user_type' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'numeric',
            ],
            'user_email' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'email',
                'unique:' . User::class . ',user_email',
            ],
            'user_name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
            'password' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'min:8',
                'confirmed',
            ],
            'user_password_confirmation' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
            ],
            'user_language' => [
                'string',
                $this->isMethod('post') ? 'required' : 'sometimes',
            ],

            // Other fields
            'user_all_clients' => [
                'bool',
            ],
            'user_company' => [
                'nullable',
                'string',
            ],
            'user_address_1' => [
                'nullable',
                'string',
            ],
            'user_address_2' => [
                'nullable',
                'string',
            ],
            'user_city' => [
                'nullable',
                'string',
            ],
            'user_state' => [
                'nullable',
                'string',
            ],
            'user_zip' => [
                'nullable',
                'string',
            ],
            'user_phone' => [
                'nullable',
                'string',
            ],
            'user_fax' => [
                'nullable',
                'string',
            ],
            'user_mobile' => [
                'nullable',
                'string',
            ],
            'user_web' => [
                'nullable',
                'string',
            ],
            'user_vat_id' => [
                'nullable',
                'string',
            ],
            'user_tax_code' => [
                'nullable',
                'string',
            ],
            'user_subscribernumber' => [
                'nullable',
                'string',
            ],
            'user_iban' => [
                'nullable',
                'string',
            ],
            // SUMEX
            'user_gln' => [
                'nullable',
                'string',
            ],
            'user_rcc' => [
                'nullable',
                'string',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
         * #40: Since we're dealing with legacy database fields
         * the `user_language` field needs to triple-checked and filled with a default
         * when null is passed
         */
        $this->merge([
            'user_language'    => $this->input('user_language') !== null && in_array($this->input('user_language'), ['system', 'english']) ? $this->input('user_language') : 'system',
            'user_all_clients' => $this->input('user_all_clients') ?? false,
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => trans('ip_validation.given_data_invalid'),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
