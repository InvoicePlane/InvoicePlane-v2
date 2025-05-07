<?php

namespace Modules\Quotes\Http\Requests;

use Modules\Quotes\Http\Requests\QuoteRequest;

use Modules\Quotes\Enums\QuoteStatus;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Models\User;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\DocumentGroup;

use Modules\Clients\Models\Relation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
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
            'customer_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . Relation::class . ',client_id',
            ],
            'document_group_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . DocumentGroup::class . ',invoice_group_id',
            ],
            'user_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . User::class . ',user_id',
            ],

            // Other Required fields
            'quote_status' => [
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
            'quote_expires_at' => [
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
