<?php

namespace Modules\Projects\Http\Requests;

use Modules\Core\Models\TaxRate;

use Modules\Projects\Http\Requests\TaskRequest;

use Modules\Projects\Models\Project;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Projects\Models\Project;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Relations
            'project_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . Project::class . ',project_id',
            ],
            'tax_rate_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . TaxRate::class . ',tax_rate_id',
            ],

            // Other Required fields
            'task_name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
            'task_description' => [
                'string',
            ],
            'task_price' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
            'task_finish_date' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
            'task_status' => [
                'boolean',
            ],
        ];
    }
}
