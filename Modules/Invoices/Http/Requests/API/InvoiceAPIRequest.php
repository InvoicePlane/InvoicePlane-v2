<?php

namespace Modules\Invoices\Http\Requests\API;

use Modules\Core\Http\Requests\API\APIRequest;

use Modules\Invoices\Http\Requests\API\InvoiceAPIRequest;

use Modules\Core\Support\Results\Invoices;


class InvoiceAPIRequest extends APIRequest
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
