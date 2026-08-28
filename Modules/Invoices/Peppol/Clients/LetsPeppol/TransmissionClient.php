<?php

namespace Modules\Invoices\Peppol\Clients\LetsPeppol;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * TransmissionClient - Tracks transmission status on LetsPeppol.
 *
 * Example response:
 * ```json
 * {
 *   "id": "doc-uuid-1234",
 *   "status": "delivered",
 *   "recipient": "0088:5412000000176",
 *   "sentAt": "2025-01-15T10:00:00Z",
 *   "deliveredAt": "2025-01-15T10:05:30Z"
 * }
 * ```
 */
class TransmissionClient extends LetsPeppolClient
{
    public function getStatus(string $id): Response
    {
        $url     = $this->buildUrl("/transmissions/{$id}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET, $url, $options);
    }
}
