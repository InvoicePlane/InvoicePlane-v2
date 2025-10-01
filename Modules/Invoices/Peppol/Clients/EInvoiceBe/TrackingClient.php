<?php

namespace Modules\Invoices\Peppol\Clients\EInvoiceBe;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * TrackingClient - Client for tracking document transmission status.
 *
 * Provides methods to monitor the delivery status of invoices sent through
 * the Peppol network, including delivery confirmations and error tracking.
 *
 * API Documentation: https://api.e-invoice.be/docs#tag/tracking
 *
 * @package Modules\Invoices\Peppol\Clients\EInvoiceBe
 */
class TrackingClient extends EInvoiceBeClient
{
    /**
     * Get transmission history for a document.
     *
     * Example response:
     * ```json
     * {
     *   "document_id": "DOC-123",
     *   "status": "delivered",
     *   "events": [
     *     {
     *       "timestamp": "2025-01-15T10:00:00Z",
     *       "event_type": "submitted",
     *       "description": "Document submitted to Peppol network"
     *     },
     *     {
     *       "timestamp": "2025-01-15T10:02:15Z",
     *       "event_type": "sent",
     *       "description": "Document sent to recipient access point"
     *     },
     *     {
     *       "timestamp": "2025-01-15T10:05:30Z",
     *       "event_type": "delivered",
     *       "description": "Document delivered to recipient"
     *     }
     *   ],
     *   "recipient_acknowledgement": {
     *     "received_at": "2025-01-15T10:05:30Z",
     *     "message_id": "MSG-456"
     *   }
     * }
     * ```
     *
     * @param string $documentId The document ID to track
     * @return Response
     */
    public function getTransmissionHistory(string $documentId): Response
    {
        $url = $this->buildUrl("/tracking/{$documentId}/history");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Get current status of a document.
     *
     * Example response:
     * ```json
     * {
     *   "document_id": "DOC-123",
     *   "current_status": "delivered",
     *   "last_updated": "2025-01-15T10:05:30Z",
     *   "recipient_participant_id": "BE:0987654321",
     *   "transmission_details": {
     *     "sent_at": "2025-01-15T10:02:15Z",
     *     "delivered_at": "2025-01-15T10:05:30Z",
     *     "access_point": "https://recipient-ap.example.com"
     *   }
     * }
     * ```
     *
     * @param string $documentId The document ID
     * @return Response
     */
    public function getStatus(string $documentId): Response
    {
        $url = $this->buildUrl("/tracking/{$documentId}/status");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Get delivery confirmation details.
     *
     * Example response:
     * ```json
     * {
     *   "document_id": "DOC-123",
     *   "delivery_confirmation": {
     *     "confirmed": true,
     *     "confirmed_at": "2025-01-15T10:05:30Z",
     *     "confirmation_type": "MDN",
     *     "message_id": "MDN-789",
     *     "recipient_signature": "..."
     *   },
     *   "processing_status": {
     *     "processed": true,
     *     "processed_at": "2025-01-15T10:10:00Z",
     *     "status_code": "AP", // Accepted
     *     "status_message": "Invoice accepted by recipient"
     *   }
     * }
     * ```
     *
     * @param string $documentId The document ID
     * @return Response
     */
    public function getDeliveryConfirmation(string $documentId): Response
    {
        $url = $this->buildUrl("/tracking/{$documentId}/confirmation");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * List all documents with optional filtering.
     *
     * Example request:
     * ```json
     * {
     *   "status": "delivered",
     *   "from_date": "2025-01-01",
     *   "to_date": "2025-01-31",
     *   "recipient": "BE:0987654321",
     *   "limit": 50,
     *   "offset": 0
     * }
     * ```
     *
     * Example response:
     * ```json
     * {
     *   "total": 150,
     *   "limit": 50,
     *   "offset": 0,
     *   "documents": [
     *     {
     *       "document_id": "DOC-123",
     *       "invoice_number": "INV-2025-001",
     *       "status": "delivered",
     *       "recipient": "BE:0987654321",
     *       "sent_at": "2025-01-15T10:00:00Z",
     *       "delivered_at": "2025-01-15T10:05:30Z"
     *     },
     *     // ... more documents
     *   ]
     * }
     * ```
     *
     * @param array<string, mixed> $filters Optional filters
     * @return Response
     */
    public function listDocuments(array $filters = []): Response
    {
        $url = $this->buildUrl('/tracking/documents');
        $options = $this->getRequestOptions([
            'payload' => $filters,
        ]);

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Get error details for a failed transmission.
     *
     * Example response:
     * ```json
     * {
     *   "document_id": "DOC-123",
     *   "status": "failed",
     *   "errors": [
     *     {
     *       "error_code": "RECIPIENT_NOT_FOUND",
     *       "error_message": "Recipient participant not found in SML",
     *       "occurred_at": "2025-01-15T10:02:00Z",
     *       "severity": "fatal"
     *     }
     *   ],
     *   "retry_possible": false,
     *   "suggested_action": "Verify recipient Peppol ID and resubmit"
     * }
     * ```
     *
     * @param string $documentId The document ID
     * @return Response
     */
    public function getErrors(string $documentId): Response
    {
        $url = $this->buildUrl("/tracking/{$documentId}/errors");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }
}
