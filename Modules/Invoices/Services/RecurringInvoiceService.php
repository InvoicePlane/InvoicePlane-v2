<?php

namespace Modules\Invoices\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\RecurringInvoice;

class RecurringInvoiceService extends BaseService
{
    public function model(): string
    {
        return RecurringInvoice::class;
    }

    public function createRecurringInvoice(array $data): Model
    {
        return parent::create($data);
    }

    public function updateRecurringInvoice(RecurringInvoice $model, array $data): RecurringInvoice
    {
        $model->update($data);

        return $model;
    }
}
