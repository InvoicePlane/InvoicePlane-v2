<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->email_template_id,
            'title' => $this->email_template_title,
        ];
    }
}
