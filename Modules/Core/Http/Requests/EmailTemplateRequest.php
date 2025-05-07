<?php

namespace Modules\Core\Http\Requests;

use Modules\Core\Http\Requests\EmailTemplateRequest;

use Illuminate\Foundation\Http\FormRequest;

class EmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
                'nullable',
                'string',
            ],
            'email_template_subject' => [
                'nullable',
                'string',
            ],
            'email_template_from_name' => [
                'nullable',
                'string',
            ],
            'email_template_from_email' => [
                'nullable',
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
                'nullable',
                'string',
            ],
        ];
    }
}
