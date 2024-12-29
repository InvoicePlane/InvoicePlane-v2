<?php

namespace Modules\Invoices\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'invoice_group_id'                => $this->invoice_group_id,
            'invoice_group_name'              => $this->invoice_group_name,
            'invoice_group_identifier_format' => $this->invoice_group_identifier_format,
            'invoice_group_next_id'           => $this->invoice_group_next_id,
            'invoice_group_left_pad'          => $this->invoice_group_left_pad,
        ];
    }
}
