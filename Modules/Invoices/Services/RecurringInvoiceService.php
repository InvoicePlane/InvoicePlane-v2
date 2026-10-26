<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\RecurringInvoice;

class RecurringInvoiceService extends BaseService
{
    public function model(): string
    {
        return RecurringInvoice::class;
    }

    public function createRecurringInvoice(array $data): RecurringInvoice
    {
        /** @var RecurringInvoice $recurringInvoice */
        $recurringInvoice = RecurringInvoice::query()->create([
            'invoice_id'   => $data['invoice_id'],
            'numbering_id' => $data['numbering_id'] ?? null,
            'frequency'    => $data['frequency'],
            'start_at'     => $data['start_at'],
            'end_at'       => $data['end_at'] ?? null,
        ]);

        return $recurringInvoice;
    }

    public function updateRecurringInvoice(RecurringInvoice $recurringInvoice, array $data): RecurringInvoice
    {
        $recurringInvoice->update([
            'invoice_id'   => $data['invoice_id'] ?? $recurringInvoice->invoice_id,
            'numbering_id' => $data['numbering_id'] ?? $recurringInvoice->numbering_id,
            'frequency'    => $data['frequency'] ?? $recurringInvoice->frequency,
            'start_at'     => $data['start_at'] ?? $recurringInvoice->start_at,
            'end_at'       => $data['end_at'] ?? $recurringInvoice->end_at,
        ]);

        return $recurringInvoice;
    }
}
