<?php

namespace Modules\Projects\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectShortResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->project_id,
            'name' => $this->project_name,
        ];
    }
}
