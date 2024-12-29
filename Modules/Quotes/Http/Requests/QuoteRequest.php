<?php

namespace Modules\Quotes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Quotes\Enums\QuoteStatus;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Relations
            'client_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . Client::class . ',client_id',
            ],
            'invoice_group_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . InvoiceGroup::class . ',invoice_group_id',
            ],
            'user_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . User::class . ',user_id',
            ],

            // Other Required fields
            'quote_status_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                Rule::in([QuoteStatus::DRAFT, QuoteStatus::SENT]),
            ],
            'quote_number' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'nullable',
            ],
            'quote_date_created' => [
                'date',
                $this->isMethod('post') ? 'required' : 'sometimes',
            ],

            // Other fields
            'quote_date_modified' => [
                'date',
            ],
            'quote_date_expires' => [
                'date',
            ],
            'quote_discount_amount' => [
                'numeric',
                'nullable',
            ],
            'quote_discount_percent' => [
                'numeric',
                'nullable',
            ],
            'quote_url_key' => [
                'string',
                'nullable',
            ],
            'quote_password' => [
                'string',
                'nullable',
            ],
            'notes' => [
                'string',
                'nullable',
            ],
        ];
    }
}
