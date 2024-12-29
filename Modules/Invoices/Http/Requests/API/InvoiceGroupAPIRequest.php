<?php

namespace Modules\Invoices\Http\Requests\API;

use Modules\Core\Http\Requests\API\APIRequest;

class InvoiceGroupAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
