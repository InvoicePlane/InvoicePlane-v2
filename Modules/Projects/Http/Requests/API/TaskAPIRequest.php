<?php

namespace Modules\Projects\Http\Requests\API;

use Modules\Core\Http\Requests\API\APIRequest;

use Modules\Projects\Http\Requests\API\TaskAPIRequest;

use Modules\Core\Models\TaxRate;

use Modules\Projects\Models\Project;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Projects\Models\Project;

class TaskAPIRequest extends APIRequest
{
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

    protected function prepareForValidation(): void
    {
        /*
         * #31: Since we're dealing with legacy database fields
         * the `task_description` field needs to become an empty string ''
         * when null is passed
         */
        $this->merge([
            'task_description' => $this->input('task_description') ?? '',
            'task_status'      => $this->input('task_status') ?? false,
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
