<?php

namespace Modules\Clients\Http\Requests\API;

use Modules\Core\Http\Requests\API\APIRequest;

use Modules\Clients\Http\Requests\API\ClientAPIRequest;

use Modules\Core\Support\Results\Clients;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Http\Requests\API\APIRequest;

class ClientAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name'          => 'required|string',
            'client_address_1'     => 'nullable|string',
            'client_address_2'     => 'nullable|string',
            'client_city'          => 'nullable|string',
            'client_state'         => 'nullable|string',
            'client_zip'           => 'nullable|string',
            'client_country'       => 'nullable|string',
            'client_phone'         => 'nullable|string',
            'client_fax'           => 'nullable|string',
            'client_mobile'        => 'nullable|string',
            'client_email'         => 'nullable|email',
            'client_web'           => 'nullable|URL',
            'client_vat_id'        => 'nullable|string',
            'client_tax_code'      => 'nullable|string',
            'client_language'      => 'nullable|string',
            'client_active'        => 'nullable|boolean',
            'client_surname'       => 'nullable|string',
            'client_avs'           => 'nullable|string',
            'client_insurednumber' => 'nullable|string',
            'client_veka'          => 'nullable|string',
            'client_birthdate'     => 'nullable|date',
            'client_gender'        => 'nullable|boolean', //TODO: does this field exist?
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => trans('ip_validation.given_data_invalid'),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
