<?php

namespace Modules\Clients\Http\Requests;

use Modules\Clients\Http\Requests\CustomerRequest;

use Modules\Core\Support\Results\Clients;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
