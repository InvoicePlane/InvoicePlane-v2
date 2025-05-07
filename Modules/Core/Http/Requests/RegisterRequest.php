<?php

namespace Modules\Core\Http\Requests;

use Modules\Core\Http\Requests\RegisterRequest;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => 'required|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'username' => 'required|alpha_dash|max:40|unique:users',
        ];
    }
}
