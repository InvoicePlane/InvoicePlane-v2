<?php

namespace Modules\Core\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailTemplateAPIRequest extends APIRequest
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
            // Required fields
            'email_template_title' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],

            // Other fields
            'email_template_type' => [
                'string',
            ],
            'email_template_subject' => [
                'string',
            ],
            'email_template_from_name' => [
                'string',
            ],
            'email_template_from_email' => [
                'email',
            ],
            'email_template_cc' => [
                'nullable',
                'string',
            ],
            'email_template_bcc' => [
                'nullable',
                'string',
            ],
            'email_template_pdf_template' => [
                'nullable',
                'string',
            ],
            'email_template_body' => [
                'string',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
         * #40: Since we're dealing with legacy database fields
         * the `email_template_type` field needs to become an empty string ''
         * when null is passed
         */
        $this->merge([
            'email_template_type'       => $this->input('email_template_type') ?? '',
            'email_template_body'       => $this->input('email_template_body') ?? '',
            'email_template_subject'    => $this->input('email_template_subject') ?? '',
            'email_template_from_name'  => $this->input('email_template_from_name') ?? '',
            'email_template_from_email' => $this->input('email_template_from_email') ?? '',
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
