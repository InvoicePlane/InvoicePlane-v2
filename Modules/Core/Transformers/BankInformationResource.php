<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BankInformationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user_iban'             => $this->bankInfo['user_iban'],
            'user_subscribernumber' => $this->bankInfo['user_subscribernumber'],
        ];
    }
}
