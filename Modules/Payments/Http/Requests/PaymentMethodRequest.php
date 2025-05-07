<?php

namespace Modules\Payments\Http\Requests;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Http\Requests\PaymentMethodRequest;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
}
