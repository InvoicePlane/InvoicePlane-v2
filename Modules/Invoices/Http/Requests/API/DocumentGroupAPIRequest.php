<?php

namespace Modules\Invoices\Http\Requests\API;

use Modules\Core\Http\Requests\API\APIRequest;

use Modules\Core\Support\Results\Invoices;

use Modules\Invoices\Http\Requests\API\DocumentGroupAPIRequest;


class DocumentGroupAPIRequest extends APIRequest
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
