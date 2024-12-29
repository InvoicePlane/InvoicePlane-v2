<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'street'  => $this->addressInfo['user_address_1'],
            'street2' => $this->addressInfo['user_address_2'],
            'city'    => $this->addressInfo['user_city'],
            'state'   => $this->addressInfo['user_state'],
            'zip'     => $this->addressInfo['user_zip'],
            'country' => $this->addressInfo['user_country'],
        ];
    }
}
