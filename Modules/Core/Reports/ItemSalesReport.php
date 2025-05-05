<?php

namespace App\IpModules\Reports\Reports;

use App\IpModules\Invoices\Models\InvoiceItem;
use App\Support\CurrencyFormatter;
use App\Support\DateFormatter;
use App\Support\NumberFormatter;
use App\Support\Statuses\InvoiceStatuses;

class ItemSalesReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null)
    {
        $results = [
            'from_date' => DateFormatter::format($fromDate),
            'to_date'   => DateFormatter::format($toDate),
            'records'   => [],
        ];

        $items = InvoiceItem::byDateRange($fromDate, $toDate)
            ->select(
                'invoice_items.name AS item_name',
                'invoice_items.quantity AS item_quantity',
                'invoice_items.price AS item_price',
                'customers.name AS client_name',
                'invoices.number AS invoice_number',
                'invoices.invoiced_at AS invoiced_at',
                'invoices.exchange_rate AS invoice_exchange_rate',
                'invoice_item_amounts.subtotal',
                'invoice_item_amounts.tax',
                'invoice_item_amounts.total'
            )
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('invoice_item_amounts', 'invoice_item_amounts.item_id', '=', 'invoice_items.id')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->where('invoices.invoice_status_id', '<>', InvoiceStatuses::getStatusId('canceled'))
            ->orderBy('invoice_items.name');

        if ($companyProfileId) {
            $items->where('invoices.company_id', $companyProfileId);
        }

        $items = $items->get();

        foreach ($items as $item) {
            $results['records'][$item->item_name]['items'][] = [
                'client_name'    => $item->client_name,
                'invoice_number' => $item->invoice_number,
                'date'           => DateFormatter::format($item->invoiced_at),
                'price'          => CurrencyFormatter::format($item->item_price / $item->invoice_exchange_rate),
                'quantity'       => NumberFormatter::format($item->item_quantity),
                'subtotal'       => CurrencyFormatter::format($item->subtotal / $item->invoice_exchange_rate),
                'tax'            => CurrencyFormatter::format($item->tax / $item->invoice_exchange_rate),
                'total'          => CurrencyFormatter::format($item->total / $item->invoice_exchange_rate),
            ];

            if (isset($results['records'][$item->item_name]['totals'])) {
                $results['records'][$item->item_name]['totals']['quantity'] += $item->quantity;
                $results['records'][$item->item_name]['totals']['subtotal'] += round($item->subtotal / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['tax'] += round($item->tax / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['total'] += round($item->total / $item->invoice_exchange_rate, 2);
            } else {
                $results['records'][$item->item_name]['totals']['quantity'] = $item->quantity;
                $results['records'][$item->item_name]['totals']['subtotal'] = round($item->subtotal / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['tax']      = round($item->tax / $item->invoice_exchange_rate, 2);
                $results['records'][$item->item_name]['totals']['total']    = round($item->total / $item->invoice_exchange_rate, 2);
            }
        }

        foreach ($results['records'] as $key => $result) {
            $results['records'][$key]['totals']['quantity'] = NumberFormatter::format($results['records'][$key]['totals']['quantity']);
            $results['records'][$key]['totals']['subtotal'] = CurrencyFormatter::format($results['records'][$key]['totals']['subtotal']);
            $results['records'][$key]['totals']['tax']      = CurrencyFormatter::format($results['records'][$key]['totals']['tax']);
            $results['records'][$key]['totals']['total']    = CurrencyFormatter::format($results['records'][$key]['totals']['total']);
        }

        return $results;
    }
}
