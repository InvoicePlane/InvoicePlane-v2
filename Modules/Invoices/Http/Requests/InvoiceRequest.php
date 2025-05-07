<?php

namespace Modules\Invoices\Http\Requests;

use Modules\Core\Support\Results\Invoices;

use Modules\Invoices\Http\Requests\InvoiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
