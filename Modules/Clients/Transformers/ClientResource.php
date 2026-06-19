<?php

namespace Modules\Clients\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->client_id,
            'client_active'        => (bool) $this->client_active,
            'company'              => $this->client_name,
            'name'                 => null,
            'client_surname'       => $this->client_surname,
            'client_language'      => $this->client_language,
            'client_gender'        => $this->client_gender,
            'client_birthdate'     => $this->client_birthdate?->format('Y-m-d'),
            'client_id'            => $this->client_id,
            'client_name'          => $this->client_name,
            'client_address_1'     => $this->client_address_1,
            'client_address_2'     => $this->client_address_2,
            'client_city'          => $this->client_city,
            'client_state'         => $this->client_state,
            'client_zip'           => $this->client_zip,
            'client_country'       => $this->client_country,
            'client_phone'         => $this->client_phone,
            'client_fax'           => $this->client_fax,
            'client_mobile'        => $this->client_mobile,
            'client_email'         => $this->client_email,
            'client_web'           => $this->client_web,
            'client_vat_id'        => $this->client_vat_id,
            'client_tax_code'      => $this->client_tax_code,
            'client_avs'           => $this->client_avs,
            'client_insurednumber' => $this->client_insurednumber,
            'client_veka'          => $this->client_veka,
        ];
    }
}
