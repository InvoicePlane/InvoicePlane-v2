<?php

namespace Modules\Invoices\Peppol\Clients\LetsPeppol;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * DocumentClient - Retrieves and manages documents on LetsPeppol.
 *
 * Example response (getDocument):
 * ```json
 * {
 *   "id": "doc-uuid-1234",
 *   "document": "PD94bWwgdmVyc2lvbj...base64...==",
 *   "documentType": "invoice",
 *   "status": "delivered"
 * }
 * ```
 */
class DocumentClient extends LetsPeppolClient
{
    public function getDocument(string $id): Response
    {
        $url     = $this->buildUrl("/documents/{$id}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET, $url, $options);
    }

    public function cancelDocument(string $id): Response
    {
        $url     = $this->buildUrl("/documents/{$id}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::DELETE, $url, $options);
    }
}
