<?php

namespace Modules\Projects\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Clients\Models\Relation;
use Modules\Core\Http\Requests\API\APIRequest;

class ProjectAPIRequest extends APIRequest
{
    /**
     * @var bool
     */
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
            'customer_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . Relation::class . ',client_id',
            ],

            // Other Required fields
            'project_name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
        ];
    }

    protected function prepareForValidation(): void {}

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => trans('ip_validation.given_data_invalid'),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
