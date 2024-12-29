<?php

namespace Modules\Products\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductUnitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->unit_id,
            'unit_name'      => $this->unit_name,
            'unit_name_plrl' => $this->unit_name_plrl,
        ];
    }
}
