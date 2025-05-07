<?php

namespace Modules\Quotes\Support;

use Modules\Core\Events\InvoiceModified;
use Modules\Core\Models\CustomField;
use Modules\Core\Support\Statuses\InvoiceStatuses;
use Modules\Core\Support\Statuses\QuoteStatuses;
use Modules\Groups\Models\Group;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;

class QuoteToInvoice
{
    public function convert($quote, $invoiceDate, $dueAt, $groupId)
    {
        $record = [
            'customer_id'       => $quote->customer_id,
            'invoiced_at'       => $invoiceDate,
            'due_at'            => $dueAt,
            'group_id'          => $groupId,
            'number'            => Group::generateNumber($groupId),
            'user_id'           => $quote->user_id,
            'invoice_status_id' => InvoiceStatuses::getStatusId('draft'),
            'terms'             => ((config('ip.convertQuoteTerms') == 'quote') ? $quote->terms : config('ip.invoiceTerms')),
            'footer'            => $quote->footer,
            'currency_code'     => $quote->currency_code,
            'exchange_rate'     => $quote->exchange_rate,
            'summary'           => $quote->summary,
            'discount'          => $quote->discount,
            'company_id'        => $quote->company_id,
        ];

        $toInvoice = Invoice::create($record);

        CustomField::copyCustomFieldValues($quote, $toInvoice);

        $quote->invoice_id      = $toInvoice->id;
        $quote->quote_status_id = QuoteStatuses::getStatusId('approved');
        $quote->save();

        foreach ($quote->quoteItems as $item) {
            $itemRecord = [
                'invoice_id'    => $toInvoice->id,
                'name'          => $item->name,
                'description'   => $item->description,
                'quantity'      => $item->quantity,
                'price'         => $item->price,
                'tax_rate_id'   => $item->tax_rate_id,
                'tax_rate_2_id' => $item->tax_rate_2_id,
                'display_order' => $item->display_order,
            ];

            InvoiceItem::create($itemRecord);
        }

        event(new InvoiceModified($toInvoice));

        return $toInvoice;
    }
}
