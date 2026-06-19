<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxInformationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'vat_id'   => $this->taxInfo['user_vat_id'],
            'tax_code' => $this->taxInfo['user_tax_code'],
        ];
    }
}
