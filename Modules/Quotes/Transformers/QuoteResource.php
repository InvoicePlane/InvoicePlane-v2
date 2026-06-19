<?php

namespace Modules\Quotes\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Clients\Transformers\ClientResource;

class QuoteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->quote_id,
            'status'       => $this->quote_status_id,
            'quote_number' => $this->quote_number,
            'created'      => $this->quote_date_created,
            'due_date'     => $this->quote_date_expires,
            'amount'       => $this->quote_discount_amount,
            'client'       => $this->whenLoaded(
                'client',
                new ClientResource($this->client)
            ),
        ];
    }
}
