<?php

namespace Modules\Core\Http\Requests;

use Modules\Core\Http\Requests\TaxRateRequest;

use Illuminate\Foundation\Http\FormRequest;

class TaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tax_rate_name'    => 'required|string|max:255',
            'tax_rate_percent' => 'required|numeric',
        ];
    }
}
