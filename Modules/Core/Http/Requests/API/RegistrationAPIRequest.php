<?php

namespace Modules\Core\Http\Requests\API;

use Modules\Core\Http\Requests\API\APIRequest;

use Modules\Core\Http\Requests\API\RegistrationAPIRequest;

class RegistrationAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'string',
                'email',
            ],
            'password' => [
                'required',
            ],
        ];
    }
}
