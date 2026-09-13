<?php

namespace Modules\Invoices\Peppol\Clients\LetsPeppol;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * InvoiceClient - Submits invoices to LetsPeppol.
 *
 * Example request:
 * ```json
 * {
 *   "document": "PD94bWwgdmVyc2lvbj...base64-encoded UBL XML...==",
 *   "documentType": "invoice",
 *   "recipient": {
 *     "scheme": "0088",
 *     "identifier": "5412000000176"
 *   }
 * }
 * ```
 *
 * Example response:
 * ```json
 * {
 *   "id": "doc-uuid-1234",
 *   "status": "accepted",
 *   "submittedAt": "2025-01-15T10:00:00Z"
 * }
 * ```
 */
class InvoiceClient extends LetsPeppolClient
{
    public function submitInvoice(array $payload): Response
    {
        $url     = $this->buildUrl('/invoices');
        $options = $this->getRequestOptions(['payload' => $payload]);

        return $this->client->request(RequestMethod::POST, $url, $options);
    }
}
