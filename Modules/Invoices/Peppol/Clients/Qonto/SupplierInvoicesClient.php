<?php

namespace Modules\Invoices\Peppol\Clients\Qonto;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

class SupplierInvoicesClient extends QontoClient
{
    public function list(array $filters = []): Response
    {
        $url     = $this->buildUrl('/supplier/invoices');
        $options = $this->getRequestOptions(['payload' => $filters]);

        return $this->client->request(RequestMethod::GET, $url, $options);
    }
}
