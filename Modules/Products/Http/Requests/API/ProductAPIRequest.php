<?php

namespace Modules\Inventory\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Http\Requests\API\APIRequest;
use Modules\Core\Models\TaxRate;
use Modules\Products\Models\ProductFamily;
use Modules\Products\Models\ProductUnit;

class ProductAPIRequest extends APIRequest
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
            'family_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . ProductFamily::class . ',family_id',
            ],
            'unit_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . ProductUnit::class . ',unit_id',
            ],
            'tax_rate_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . TaxRate::class . ',tax_rate_id',
            ],

            // Other Required fields
            'product_sku' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],
            'product_name' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
            ],

            // Other fields
            'product_description' => [
                'string',
            ],
            'product_price' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'numeric',
            ],
            'purchase_price' => [
                'numeric',
            ],
            'provider_name' => [
                'string',
            ],

            // other stuff (Sumex)
            'product_tariff' => [
                'numeric',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
         * #24: Since we're dealing with legacy database fields
         * the `product_description` field needs to become an empty string ''
         * when null is passed
         */
        $this->merge([
            'product_description' => $this->input('product_description') ?? '',
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
