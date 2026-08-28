<?php

namespace Modules\Invoices\Peppol\Clients\LetsPeppol;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * CreditNoteClient - Submits credit notes to LetsPeppol.
 *
 * Example request:
 * ```json
 * {
 *   "document": "PD94bWwgdmVyc2lvbj...base64-encoded UBL XML...==",
 *   "documentType": "creditNote",
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
 *   "id": "doc-uuid-5678",
 *   "status": "accepted",
 *   "submittedAt": "2025-01-15T10:00:00Z"
 * }
 * ```
 */
class CreditNoteClient extends LetsPeppolClient
{
    public function submitCreditNote(array $payload): Response
    {
        $url     = $this->buildUrl('/credit-notes');
        $options = $this->getRequestOptions(['payload' => $payload]);

        return $this->client->request(RequestMethod::POST, $url, $options);
    }
}
