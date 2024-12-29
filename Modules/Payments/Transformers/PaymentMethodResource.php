<?php

namespace Modules\Payments\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->payment_method_id,
            'name' => $this->payment_method_name,
        ];
    }
}
