<?php

namespace Modules\Invoices\Peppol\Clients\Storecove;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * DocumentSubmissionsClient - Submits documents to Storecove for processing.
 *
 * Handles submission of UBL XML invoices and retrieval of submission evidence/status.
 *
 * API Documentation: https://www.storecove.com/documentation/api/index.html#document_submissions
 */
class DocumentSubmissionsClient extends StorecoveClient
{
    /**
     * Submit a document (invoice) to Storecove.
     *
     * Example request:
     * ```json
     * {
     *   "legalEntityId": 12345,
     *   "document": {
     *     "rawDocumentData": {
     *       "document": "PD94bWwgdmVyc2lvbj...base64-encoded UBL XML...==",
     *       "documentType": "invoice",
     *       "parseStrategy": "ubl"
     *     }
     *   },
     *   "routing": {
     *     "eIdentifiers": [
     *       {
     *         "scheme": "0088",
     *         "id": "5412000000176"
     *       }
     *     ]
     *   }
     * }
     * ```
     *
     * Example response:
     * ```json
     * {
     *   "entity": {
     *     "guid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
     *     "submittedAt": "2025-01-15T10:00:00Z",
     *     "status": "accepted"
     *   }
     * }
     * ```
     *
     * @param array $payload Submission payload with document and routing data
     *
     * @return Response
     */
    public function submitDocument(array $payload): Response
    {
        $url     = $this->buildUrl('/document_submissions');
        $options = $this->getRequestOptions([
            'payload' => $payload,
        ]);

        return $this->client->request(RequestMethod::POST, $url, $options);
    }

    /**
     * Get evidence for a document submission (sending/receiving evidence).
     *
     * Example response:
     * ```json
     * {
     *   "guid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
     *   "evidenceType": "sending",
     *   "evidence": "PD94bWwgdmVyc2lvbj...base64...==",
     *   "timestamp": "2025-01-15T10:05:30Z",
     *   "status": "delivered"
     * }
     * ```
     *
     * @param string $submissionGuid The GUID of the document submission
     * @param string $type           The type of evidence ('sending' or 'receiving')
     *
     * @return Response
     */
    public function getEvidence(string $submissionGuid, string $type = 'sending'): Response
    {
        $url     = $this->buildUrl("/document_submissions/{$submissionGuid}/evidence/{$type}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET, $url, $options);
    }
}
