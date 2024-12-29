<?php

namespace Modules\Quotes\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Clients\Models\Client;
use Modules\Core\Http\Requests\API\APIRequest;
use Modules\Core\Models\User;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Quotes\Enums\QuoteStatus;

class QuoteAPIRequest extends APIRequest
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

    protected function prepareForValidation(): void
    {
        /*
         * #40: Since we're dealing with legacy database fields
         * the `quote_date_created` has to be filled with a date.
         * Then, after that, also fill `date_modified`
         * `date_expires` can only be empty string, when null is passed,
         */
        $this->merge([
            'quote_date_created'  => $this->input('quote_date_created') ?? now(),
            'quote_date_modified' => $this->input('quote_date_modified') ?? now(),
            'quote_date_expires'  => $this->input('quote_date_expires') ?? '',
            'quote_url_key'       => $this->input('quote_url_key') ?? '',
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
