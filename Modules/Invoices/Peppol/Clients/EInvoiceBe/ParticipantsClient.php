<?php

namespace Modules\Invoices\Peppol\Clients\EInvoiceBe;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * ParticipantsClient - Client for Peppol participant search operations.
 *
 * This client provides methods to search for Peppol participants by their
 * identifiers, useful for validating customer Peppol IDs before sending invoices.
 *
 * API Documentation: https://api.e-invoice.be/docs#tag/participants
 */
class ParticipantsClient extends EInvoiceBeClient
{
    /**
     * Search for a Peppol participant by identifier.
     *
     * Validates if a given Peppol ID is registered in the network.
     *
     * Example request:
     * ```json
     * {
     *   "participant_id": "BE:0123456789",
     *   "scheme": "BE:CBE"
     * }
     * ```
     *
     * Example response:
     * ```json
     * {
     *   "participant_id": "BE:0123456789",
     *   "scheme": "BE:CBE",
     *   "registered": true,
     *   "capabilities": ["invoice", "credit_note"],
     *   "service_metadata": {
     *     "endpoint": "https://access-point.example.com",
     *     "certificate": "..."
     *   }
     * }
     * ```
     *
     * @param string      $participantId The Peppol participant identifier
     * @param string|null $scheme        The identifier scheme (e.g., 'BE:CBE')
     *
     * @return Response
     */
    public function searchParticipant(string $participantId, ?string $scheme = null): Response
    {
        $url     = $this->buildUrl('/participants/search');
        $options = $this->getRequestOptions([
            'payload' => array_filter([
                'participant_id' => $participantId,
                'scheme'         => $scheme,
            ]),
        ]);

        return $this->client->request(RequestMethod::POST->value, $url, $options);
    }

    /**
     * Lookup participant by identifier (alternative endpoint).
     *
     * Example response:
     * ```json
     * {
     *   "id": "BE:0123456789",
     *   "scheme": "BE:CBE",
     *   "name": "Example Company",
     *   "country": "BE",
     *   "capabilities": {
     *     "receives_invoices": true,
     *     "receives_credit_notes": true,
     *     "receives_orders": false
     *   }
     * }
     * ```
     *
     * @param string $participantId The participant identifier (format: scheme:id)
     *
     * @return Response
     */
    public function lookupParticipant(string $participantId): Response
    {
        $url     = $this->buildUrl("/participants/{$participantId}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Check if a participant can receive a specific document type.
     *
     * Example response:
     * ```json
     * {
     *   "participant_id": "BE:0123456789",
     *   "document_type": "invoice",
     *   "can_receive": true,
     *   "endpoint": "https://access-point.example.com/receive"
     * }
     * ```
     *
     * @param string $participantId The participant identifier
     * @param string $documentType  The document type (e.g., 'invoice', 'credit_note')
     *
     * @return Response
     */
    public function checkCapability(string $participantId, string $documentType): Response
    {
        $url     = $this->buildUrl("/participants/{$participantId}/capabilities");
        $options = $this->getRequestOptions([
            'payload' => [
                'document_type' => $documentType,
            ],
        ]);

        return $this->client->request(RequestMethod::POST->value, $url, $options);
    }

    /**
     * Get service metadata for a participant.
     *
     * Example response:
     * ```json
     * {
     *   "participant_id": "BE:0123456789",
     *   "service_metadata": {
     *     "endpoint_url": "https://access-point.example.com",
     *     "certificate_info": {
     *       "subject": "CN=Example Company",
     *       "issuer": "CN=Peppol CA",
     *       "valid_from": "2024-01-01",
     *       "valid_to": "2026-01-01"
     *     },
     *     "transport_profile": "peppol-transport-as4-v2_0"
     *   }
     * }
     * ```
     *
     * @param string $participantId The participant identifier
     *
     * @return Response
     */
    public function getServiceMetadata(string $participantId): Response
    {
        $url     = $this->buildUrl("/participants/{$participantId}/metadata");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }
}
