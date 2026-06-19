<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactInformationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'phone'  => $this->contactInfo['user_phone'],
            'mobile' => $this->contactInfo['user_mobile'],
            'fax'    => $this->contactInfo['user_fax'],
            'web'    => $this->contactInfo['user_web'],
        ];
    }
}
