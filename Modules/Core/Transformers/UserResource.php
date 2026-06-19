<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $request->addressInfo = [
            'user_address_1' => $this->user_address_1,
            'user_address_2' => $this->user_address_2,
            'user_city'      => $this->user_city,
            'user_state'     => $this->user_state,
            'user_zip'       => $this->user_zip,
            'user_country'   => $this->user_country,
        ];

        $request->bankInfo = [
            'user_subscribernumber' => $this->user_subscribernumber,
            'user_iban'             => $this->user_iban,
        ];

        $request->contactInfo = [
            'user_phone'  => $this->user_phone,
            'user_mobile' => $this->user_mobile,
            'user_fax'    => $this->user_fax,
            'user_web'    => $this->user_web,
        ];

        $request->taxInfo = [
            'user_vat_id'   => $this->user_vat_id,
            'user_tax_code' => $this->user_tax_code,
        ];

        return [
            'id'            => $this->user_id,
            'user_type'     => $this->user_type,
            'is_active'     => $this->user_active,
            'date_created'  => $this->user_date_created,
            'date_modified' => $this->user_date_modified,
            'language'      => $this->user_language,
            'name'          => $this->user_name,
            'company'       => $this->user_company,
            'user_email'    => $this->user_email,
            'all_clients'   => $this->user_all_clients,
            'address'       => new AddressResource($request),
            'bank'          => new BankInformationResource($request),
            'contact'       => new ContactInformationResource($request),
            'tax'           => new TaxInformationResource($request),
        ];
    }
}
