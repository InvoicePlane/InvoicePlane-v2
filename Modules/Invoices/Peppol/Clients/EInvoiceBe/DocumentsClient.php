<?php

namespace Modules\Invoices\Peppol\Clients\EInvoiceBe;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * DocumentsClient - Client for managing documents in e-invoice.be API.
 *
 * This client handles all document-related operations for the e-invoice.be
 * Peppol provider, including submitting invoices to the Peppol network.
 *
 * @see https://api.e-invoice.be/docs#tag/documents/post/api/documents/ API Documentation
 */
class DocumentsClient extends EInvoiceBeClient
{
    /**
     * Submit a document (invoice) to the Peppol network.
     *
     * This method sends an invoice document to the e-invoice.be API which will
     * then transmit it through the Peppol network to the recipient.
     *
     * Example request JSON:
     * ```json
     * {
     *   "document_type": "invoice",
     *   "invoice_number": "INV-2024-001",
     *   "issue_date": "2024-01-15",
     *   "due_date": "2024-02-14",
     *   "currency_code": "EUR",
     *   "supplier": {
     *     "name": "Company Name",
     *     "vat_number": "BE0123456789"
     *   },
     *   "customer": {
     *     "name": "Customer Name",
     *     "endpoint_id": "BE:0987654321",
     *     "endpoint_scheme": "BE:CBE"
     *   },
     *   "invoice_lines": [...],
     *   "legal_monetary_total": {...}
     * }
     * ```
     *
     * Example response JSON:
     * ```json
     * {
     *   "document_id": "DOC-123456",
     *   "status": "submitted",
     *   "created_at": "2024-01-15T10:30:00Z"
     * }
     * ```
     *
     * @param array<string, mixed> $documentData The document data to submit
     *
     * @return Response The API response
     *
     * @throws \Illuminate\Http\Client\RequestException    If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function submitDocument(array $documentData): Response
    {
        $options = array_merge($this->getRequestOptions(), [
            'payload' => $documentData,
        ]);

        /* The client.request() will throw RequestException for unsuccessful responses */
        return $this->client->request(
            RequestMethod::POST,
            $this->buildUrl('api/documents'),
            $options
        );
    }

    /**
     * Get a document by its ID.
     *
     * Retrieves the details and status of a previously submitted document.
     *
     * Example response JSON:
     * ```json
     * {
     *   "document_id": "DOC-123456",
     *   "status": "delivered",
     *   "invoice_number": "INV-2024-001",
     *   "created_at": "2024-01-15T10:30:00Z",
     *   "delivered_at": "2024-01-15T11:45:00Z"
     * }
     * ```
     *
     * @param string $documentId The unique identifier of the document
     *
     * @return Response The API response containing document details
     *
     * @throws \Illuminate\Http\Client\RequestException    If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function getDocument(string $documentId): Response
    {
        try {
            return $this->client->request(
                RequestMethod::GET,
                $this->buildUrl("api/documents/{$documentId}"),
                $this->getRequestOptions()
            );
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // For 404 errors, return the response so caller can inspect
            if ($e->response?->status() === 404) {
                return $e->response;
            }
            // For authentication (401) and other errors, let the exception propagate
            throw $e;
        }
    }

    /**
     * Get the status of a document.
     *
     * Checks the current transmission status of a document in the Peppol network.
     *
     * Example response JSON:
     * ```json
     * {
     *   "status": "delivered",
     *   "timestamp": "2024-01-15T11:45:00Z",
     *   "message": "Document successfully delivered to recipient"
     * }
     * ```
     *
     * @param string $documentId The unique identifier of the document
     *
     * @return Response The API response containing status information
     *
     * @throws \Illuminate\Http\Client\RequestException    If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function getDocumentStatus(string $documentId): Response
    {
        /* The client.request() will throw RequestException for unsuccessful responses */
        return $this->client->request(
            RequestMethod::GET,
            $this->buildUrl("api/documents/{$documentId}/status"),
            $this->getRequestOptions()
        );
    }

    /**
     * List all documents with optional filters.
     *
     * Retrieves a paginated list of documents submitted through the API.
     *
     * Example response JSON:
     * ```json
     * {
     *   "documents": [
     *     {"document_id": "DOC-1", "status": "delivered"},
     *     {"document_id": "DOC-2", "status": "pending"}
     *   ],
     *   "total": 25,
     *   "page": 1,
     *   "per_page": 10
     * }
     * ```
     *
     * @param array<string, mixed> $filters Optional filters (e.g., status, date range)
     *
     * @return Response The API response containing list of documents
     *
     * @throws \Illuminate\Http\Client\RequestException    If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function listDocuments(array $filters = []): Response
    {
        $options = array_merge($this->getRequestOptions(), [
            'payload' => $filters,
        ]);

        return $this->client->request(
            RequestMethod::GET,
            $this->buildUrl('api/documents'),
            $options
        );
    }

    /**
     * Cancel a document submission.
     *
     * Attempts to cancel a document that has been submitted but not yet delivered.
     *
     * @param string $documentId The unique identifier of the document to cancel
     *
     * @return Response The API response
     *
     * @throws \Illuminate\Http\Client\RequestException    If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function cancelDocument(string $documentId): Response
    {
        /* The client.request() will throw RequestException for unsuccessful responses */
        return $this->client->request(
            RequestMethod::DELETE,
            $this->buildUrl("api/documents/{$documentId}"),
            $this->getRequestOptions()
        );
    }
}
