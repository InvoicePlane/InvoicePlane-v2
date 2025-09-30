<?php

namespace Modules\Invoices\Peppol\Clients\EInvoiceBe;

use Illuminate\Http\Client\Response;

/**
 * DocumentsClient - Client for managing documents in e-invoice.be API.
 *
 * This client handles all document-related operations for the e-invoice.be
 * Peppol provider, including submitting invoices to the Peppol network.
 *
 * @see https://api.e-invoice.be/docs#tag/documents/post/api/documents/ API Documentation
 * @package Modules\Invoices\Peppol\Clients\EInvoiceBe
 */
class DocumentsClient extends EInvoiceBeClient
{
    /**
     * Submit a document (invoice) to the Peppol network.
     *
     * This method sends an invoice document to the e-invoice.be API which will
     * then transmit it through the Peppol network to the recipient.
     *
     * @param array<string, mixed> $documentData The document data to submit
     * @return Response The API response
     *
     * @throws \Illuminate\Http\Client\RequestException If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function submitDocument(array $documentData): Response
    {
        return $this->client->post('api/documents', $documentData);
    }

    /**
     * Get a document by its ID.
     *
     * Retrieves the details and status of a previously submitted document.
     *
     * @param string $documentId The unique identifier of the document
     * @return Response The API response containing document details
     *
     * @throws \Illuminate\Http\Client\RequestException If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function getDocument(string $documentId): Response
    {
        return $this->client->get("api/documents/{$documentId}");
    }

    /**
     * Get the status of a document.
     *
     * Checks the current transmission status of a document in the Peppol network.
     *
     * @param string $documentId The unique identifier of the document
     * @return Response The API response containing status information
     *
     * @throws \Illuminate\Http\Client\RequestException If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function getDocumentStatus(string $documentId): Response
    {
        return $this->client->get("api/documents/{$documentId}/status");
    }

    /**
     * List all documents with optional filters.
     *
     * Retrieves a paginated list of documents submitted through the API.
     *
     * @param array<string, mixed> $filters Optional filters (e.g., status, date range)
     * @return Response The API response containing list of documents
     *
     * @throws \Illuminate\Http\Client\RequestException If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function listDocuments(array $filters = []): Response
    {
        return $this->client->get('api/documents', $filters);
    }

    /**
     * Cancel a document submission.
     *
     * Attempts to cancel a document that has been submitted but not yet delivered.
     *
     * @param string $documentId The unique identifier of the document to cancel
     * @return Response The API response
     *
     * @throws \Illuminate\Http\Client\RequestException If the request fails
     * @throws \Illuminate\Http\Client\ConnectionException If there's a connection issue
     */
    public function cancelDocument(string $documentId): Response
    {
        return $this->client->delete("api/documents/{$documentId}");
    }
}
