<?php

namespace Modules\Products\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductFamilyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->family_id,
            'product_family' => $this->family_name,
        ];
    }
}
