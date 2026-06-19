<?php

namespace Modules\Invoices\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'invoice_id'               => $this->invoice_id,
            'user_id'                  => $this->user_id,
            'client_id'                => $this->client_id,
            'invoice_group_id'         => $this->invoice_group_id,
            'invoice_status_id'        => $this->invoice_status_id,
            'is_read_only'             => $this->is_read_only,
            'invoice_password'         => $this->invoice_password,
            'invoice_date_created'     => $this->invoice_date_created,
            'invoice_time_created'     => $this->invoice_time_created,
            'invoice_date_modified'    => $this->invoice_date_modified,
            'invoice_date_due'         => $this->invoice_date_due,
            'invoice_number'           => $this->invoice_number,
            'invoice_discount_amount'  => $this->invoice_discount_amount,
            'invoice_discount_percent' => $this->invoice_discount_percent,
            'invoice_terms'            => $this->invoice_terms,
            'invoice_url_key'          => $this->invoice_url_key,
            'payment_method'           => $this->payment_method,
            'creditinvoice_parent_id'  => $this->creditinvoice_parent_id,
        ];
    }
}
