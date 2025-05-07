<?php

namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Relations
            'family_id' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'exists:' . ProductCategory::class . ',family_id',
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
            'item_name' => [
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
}
