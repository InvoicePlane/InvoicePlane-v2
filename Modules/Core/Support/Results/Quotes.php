<?php

namespace Modules\Core\Support\Results;

use Modules\Core\Support\Results\SourceInterface;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;


class Quotes implements SourceInterface
{
    public function getResults($params = [])
    {
        $quote = Quote::select(
            'quotes.number',
            'quotes.created_at',
            'quotes.updated_at',
            'quotes.expires_at',
            'quotes.terms',
            'quotes.footer',
            'quotes.url_key',
            'quotes.currency_code',
            'quotes.exchange_rate',
            'quotes.template',
            'quotes.summary',
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
            'quote_amounts.subtotal',
            'quote_amounts.tax',
            'quote_amounts.total'
        )
            ->join('quote_amounts', 'quote_amounts.quote_id', '=', 'quotes.id')
            ->join('customers', 'customers.id', '=', 'quotes.customer_id')
            ->join('groups', 'groups.id', '=', 'quotes.group_id')
            ->join('users', 'users.id', '=', 'quotes.user_id')
            ->join('companies', 'company_profiles.id', '=', 'quotes.company_id')
            ->orderBy('number');

        return $quote->get()->toArray();
    }
}
