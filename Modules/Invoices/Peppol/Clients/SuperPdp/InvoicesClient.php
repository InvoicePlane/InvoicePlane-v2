<?php

namespace Modules\Invoices\Peppol\Clients\SuperPdp;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * InvoicesClient - Submits PDF invoices to SuperPDP.
 *
 * Example response:
 * ```json
 * {
 *   "externalId": "doc-123",
 *   "status": "accepted",
 *   "submittedAt": "2025-01-15T10:00:00Z"
 * }
 * ```
 */
class InvoicesClient extends SuperPdpClient
{
    public function sendInvoice(string $pdfBinary, array $query = []): Response
    {
        $url     = $this->buildUrl('/invoices');
        $options = $this->getRequestOptions([
            'payload' => $pdfBinary,
            'headers' => ['Content-Type' => 'application/pdf'],
        ]);

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->client->request(RequestMethod::POST, $url, $options);
    }

    public function getInvoiceStatus(string $externalId): Response
    {
        $url     = $this->buildUrl("/invoices/{$externalId}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET, $url, $options);
    }

    public function listEvents(array $filters = []): Response
    {
        $url     = $this->buildUrl('/events');
        $options = $this->getRequestOptions(['payload' => $filters]);

        return $this->client->request(RequestMethod::GET, $url, $options);
    }
}
