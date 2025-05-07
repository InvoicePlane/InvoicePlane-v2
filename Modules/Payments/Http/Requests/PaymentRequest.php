<?php

namespace Modules\Payments\Http\Requests;

use Modules\Payments\Models\PaymentMethod;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Http\Requests\PaymentRequest;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Support\Results\Invoices;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\PaymentMethod;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
}
