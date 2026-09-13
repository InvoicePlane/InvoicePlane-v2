<?php

namespace Modules\Invoices\Peppol\Clients\Storecove;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * ReceivedDocumentsClient - Retrieves received documents from Storecove.
 *
 * Handles fetching of received invoices and documents that have been processed by Storecove.
 *
 * API Documentation: https://www.storecove.com/documentation/api/index.html#received_documents
 */
class ReceivedDocumentsClient extends StorecoveClient
{
    /**
     * Get the original received document.
     *
     * Example response:
     * ```json
     * {
     *   "guid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
     *   "document": "PD94bWwgdmVyc2lvbj...base64...==",
     *   "documentType": "Invoice",
     *   "sender": {
     *     "scheme": "0088",
     *     "identifier": "5412000000176"
     *   },
     *   "receivedAt": "2025-01-15T10:00:00Z"
     * }
     * ```
     *
     * @param string $guid The GUID of the received document
     *
     * @return Response
     */
    public function getOriginal(string $guid): Response
    {
        $url     = $this->buildUrl("/received_documents/{$guid}/document");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET, $url, $options);
    }
}
