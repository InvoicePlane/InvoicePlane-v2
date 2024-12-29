<?php

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Clients\Models\Client;

class ProjectRequest extends FormRequest
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

            // Other Required fields
            'project_name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
        ];
    }
}
