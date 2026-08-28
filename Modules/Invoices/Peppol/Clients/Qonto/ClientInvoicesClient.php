<?php

namespace Modules\Invoices\Peppol\Clients\Qonto;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

class ClientInvoicesClient extends QontoClient
{
    public function import(string $pdfPath): Response
    {
        $url     = $this->buildUrl('/client/invoices/import');
        $options = $this->getRequestOptions([
            'headers' => ['Content-Type' => 'multipart/form-data'],
        ]);

        return $this->client->request(RequestMethod::POST, $url, $options);
    }

    public function sendByEinvoice(string $clientInvoiceId): Response
    {
        $url     = $this->buildUrl("/client/invoices/{$clientInvoiceId}/send/einvoice");
        $options = $this->getRequestOptions(['payload' => []]);

        return $this->client->request(RequestMethod::POST, $url, $options);
    }

    public function getStatus(string $clientInvoiceId): Response
    {
        $url     = $this->buildUrl("/client/invoices/{$clientInvoiceId}/status");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET, $url, $options);
    }
}
