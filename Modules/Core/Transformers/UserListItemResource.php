<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserListItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $request->contactInfo = [
            'user_phone'  => $this->user_phone,
            'user_mobile' => $this->user_mobile,
            'user_fax'    => $this->user_fax,
            'user_web'    => $this->user_web,
        ];

        return [
            'id'        => $this->user_id,
            'user_type' => $this->user_type,
            'is_active' => $this->user_active,
            'language'  => $this->user_language,
            'name'      => $this->user_name,
            'company'   => $this->user_company,
            'email'     => $this->user_email,
            'contact'   => new ContactInformationResource($request),
        ];
    }
}
