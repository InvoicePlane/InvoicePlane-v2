<?php

namespace Modules\Products\Http\Requests;

use Modules\Products\Http\Requests\ProductUnitRequest;

use Illuminate\Foundation\Http\FormRequest;

class ProductUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_name' => ['required'],
        ];
    }
}
