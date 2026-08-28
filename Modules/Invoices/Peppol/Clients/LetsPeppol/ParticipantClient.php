<?php

namespace Modules\Invoices\Peppol\Clients\LetsPeppol;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * ParticipantClient - Validates Peppol participants on LetsPeppol.
 *
 * Example request:
 * ```
 * GET /participants/0088/5412000000176
 * ```
 *
 * Example response:
 * ```json
 * {
 *   "scheme": "0088",
 *   "identifier": "5412000000176",
 *   "active": true,
 *   "name": "Example Company"
 * }
 * ```
 */
class ParticipantClient extends LetsPeppolClient
{
    public function lookupParticipant(string $scheme, string $id): Response
    {
        $url     = $this->buildUrl("/participants/{$scheme}/{$id}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET, $url, $options);
    }
}
