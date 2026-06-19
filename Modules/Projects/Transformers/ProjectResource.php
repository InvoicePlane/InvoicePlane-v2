<?php

namespace Modules\Projects\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Clients\Transformers\ClientResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->project_id,
            'client' => $this->whenLoaded(
                'client',
                new ClientResource($this->client)
            ),
            'name' => $this->project_name,
        ];
    }
}
