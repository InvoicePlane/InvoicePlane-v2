<?php

namespace Modules\Core\Http\Requests;

use Modules\Core\Http\Requests\UserRequest;

use Modules\Core\Models\User;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Models\User;

class UserRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
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
}
