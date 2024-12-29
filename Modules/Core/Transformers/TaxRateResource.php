<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxRateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->tax_rate_id,
            'name'       => $this->tax_rate_name,
            'percentage' => $this->tax_rate_percent,
        ];
    }
}
