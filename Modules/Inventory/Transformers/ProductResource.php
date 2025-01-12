<?php

namespace Modules\Products\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\TaxRateResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->product_id,
            'family' => $this->whenLoaded(
                'family',
                new ProductFamilyResource(
                    $this->family
                )
            ),
            'product_sku'         => $this->product_sku,
            'product_name'        => $this->product_name,
            'product_description' => $this->product_description,
            'product_price'       => $this->product_price,
            'unit'                => $this->whenLoaded(
                'unit',
                new ProductUnitResource($this->unit)
            ),
            'tax_rate' => $this->whenLoaded(
                'taxRate',
                new TaxRateResource($this->taxRate)
            ),
            'provider_name'  => $this->provider_name ?? '',
            'purchase_price' => $this->purchase_price ?? '',
            'product_tariff' => $this->product_tariff ?? '',
        ];
    }
}
