<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxRateSelectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->tax_rate_id,
        ];
    }
}
