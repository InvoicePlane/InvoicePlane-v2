<?php

namespace Modules\Payments\Http\Requests\API;

use Modules\Payments\Models\PaymentMethod;

use Modules\Core\Http\Requests\API\APIRequest;

use Modules\Core\Support\Results\Payments;

use Modules\Invoices\Models\Invoice;

use Modules\Payments\Http\Requests\API\PaymentAPIRequest;

use Modules\Core\Support\Results\Invoices;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Http\Requests\API\APIRequest;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\PaymentMethod;

class PaymentAPIRequest extends APIRequest
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
            // Relations
            'invoice_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . Invoice::class . ',invoice_id',
            ],
            'payment_method_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . PaymentMethod::class . ',payment_method_id',
            ],

            // Other Required fields
            'payment_date' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'date',
            ],
            'payment_amount' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'numeric',
            ],

            // Other fields
            'payment_note' => [
                'string',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
         * #40: Since we're dealing with legacy database fields
         * the `payment_note` field needs to become an empty string ''
         * when null is passed
         */
        $this->merge([
            'payment_note' => $this->input('payment_note') ?? '',
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
