<?php

namespace Modules\Projects\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\TaxRateSelectResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->task_id,
            'name'        => $this->task_name,
            'status'      => $this->task_status,
            'description' => $this->task_description,
            'finishDate'  => $this->task_finish_date,
            'price'       => $this->task_price,
            'project'     => $this->whenLoaded(
                'project',
                new ProjectShortResource($this->project)
            ),
            'taxRate' => $this->whenLoaded(
                'taxRate',
                new TaxRateSelectResource($this->taxRate)
            ),
        ];
    }
}
