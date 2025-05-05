<?php

namespace App\IpModules\Exports\Support\Results;

use App\IpModules\Invoices\Models\Invoice;

class Invoices implements SourceInterface
{
    public function getResults($params = [])
    {
        $invoice = Invoice::select(
            'invoices.number',
            'invoices.created_at',
            'invoices.updated_at',
            'invoices.invoiced_at',
            'invoices.due_at',
            'invoices.terms',
            'invoices.footer',
            'invoices.url_key',
            'invoices.currency_code',
            'invoices.exchange_rate',
            'invoices.template',
            'invoices.summary',
            'groups.name AS group',
            'customers.name AS client_name',
            'customers.email AS client_email',
            'customers.address AS client_address',
            'customers.city AS client_city',
            'customers.state AS client_state',
            'customers.zip AS client_zip',
            'customers.country AS client_country',
            'users.name AS user_name',
            'users.email AS user_email',
            'company_profiles.company AS company',
            'company_profiles.address AS company_address',
            'company_profiles.city AS company_city',
            'company_profiles.state AS company_state',
            'company_profiles.zip AS company_zip',
            'company_profiles.country AS company_country',
            'invoice_amounts.subtotal',
            'invoice_amounts.tax',
            'invoice_amounts.total',
            'invoice_amounts.paid',
            'invoice_amounts.balance'
        )
            ->join('invoice_amounts', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->join('groups', 'groups.id', '=', 'invoices.group_id')
            ->join('users', 'users.id', '=', 'invoices.user_id')
            ->join('companies', 'company_profiles.id', '=', 'invoices.company_id')
            ->orderBy('number');

        return $invoice->get()->toArray();
    }
}
