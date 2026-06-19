<?php

namespace Modules\Payments\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Invoices\Transformers\InvoiceResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->payment_id,
            'invoice' => $this->whenLoaded(
                'invoice',
                new InvoiceResource($this->invoice)
            ),
            'payment_method' => $this->whenLoaded(
                'paymentMethod',
                new PaymentMethodResource($this->paymentMethod)
            ),
            'payment_date' => $this->payment_date,
            'amount'       => $this->payment_amount,
        ];
    }
}
