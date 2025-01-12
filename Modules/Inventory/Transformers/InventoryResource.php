<?php

namespace Modules\Inventory\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [];
    }
}
