<?php

namespace Modules\Invoices\Peppol\Services;

use Illuminate\Http\Client\RequestException;
use InvalidArgumentException;
use Modules\Invoices\Http\Traits\LogsApiRequests;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;
use Modules\Invoices\Peppol\FormatHandlers\FormatHandlerFactory;
use RuntimeException;

/**
 * PeppolService - Service for managing Peppol document transmission.
 *
 * This service handles the business logic for sending invoices through the
 * Peppol network. It coordinates between the invoice data, format handlers,
 * the Peppol client, and provides a clean interface for the application.
 *
 * Uses the Strategy Pattern to select appropriate format handlers based on
 * customer requirements and country-specific regulations.
 */
class PeppolService
{
    use LogsApiRequests;

    /**
     * The Peppol documents client.
     *
     * @var DocumentsClient
     */
    protected DocumentsClient $documentsClient;

    /**
     * Constructor.
     *
     * @param DocumentsClient $documentsClient The documents client for Peppol
     */
    public function __construct(DocumentsClient $documentsClient)
    {
        $this->documentsClient = $documentsClient;
    }

    /**
     * Send an invoice to the Peppol network.
     *
     * This method takes an invoice, prepares it using the appropriate format handler,
     * and sends it through the Peppol network via the configured provider.
     *
     * @param Invoice              $invoice The invoice to send
     * @param array<string, mixed> $options Optional options for the transmission
     *
     * @return array<string, mixed> Response data including document ID and status
     *
     * @throws RequestException         If the Peppol API request fails
     * @throws InvalidArgumentException If the invoice data is invalid
     * @throws RuntimeException         If no format handler is available
     */
    public function sendInvoiceToPeppol(Invoice $invoice, array $options = []): array
    {
        // Validate invoice before processing
        $this->validateInvoice($invoice);

        // Get the appropriate format handler for this invoice
        $formatHandler = FormatHandlerFactory::createForInvoice($invoice);

        // Validate invoice before sending
        $validationErrors = $formatHandler->validate($invoice);
        if ( ! empty($validationErrors)) {
            throw new InvalidArgumentException('Invoice validation failed: ' . implode(', ', $validationErrors));
        }

        // Transform invoice using the format handler
        $documentData = $formatHandler->transform($invoice, $options);

        $this->logRequest('Peppol', 'POST /documents', [
            'invoice_id'       => $invoice->id,
            'invoice_number'   => $invoice->invoice_number,
            'format'           => $formatHandler->getFormat()->value,
            'customer_country' => $invoice->customer->country_code,
        ]);

        try {
            $response     = $this->documentsClient->submitDocument($documentData);
            $responseData = $response->json();

            // If response is not successful, throw exception
            if ( ! $response->successful()) {
                $response->throw();
            }

            $this->logResponse('Peppol', 'POST /documents', $response->status(), $responseData);

            return [
                'success'     => true,
                'document_id' => $responseData['document_id'] ?? null,
                'status'      => $responseData['status'] ?? 'submitted',
                'format'      => $formatHandler->getFormat()->value,
                'message'     => 'Invoice successfully submitted to Peppol network',
                'response'    => $responseData,
            ];
        } catch (RequestException $e) {
            $this->logError('Request', 'POST', '/documents', $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'format'     => $formatHandler->getFormat()->value,
            ]);

            throw $e;
        }
    }

    /**
     * Get the status of a Peppol document.
     *
     * Retrieves the current transmission status of a document in the Peppol network.
     *
     * @param string $documentId The Peppol document ID
     *
     * @return array<string, mixed> Status information
     *
     * @throws RequestException If the API request fails
     */
    public function getDocumentStatus(string $documentId): array
    {
        $this->logRequest('Peppol', "GET /documents/{$documentId}/status", [
            'document_id' => $documentId,
        ]);

        try {
            $response     = $this->documentsClient->getDocumentStatus($documentId);
            $responseData = $response->json();

            $this->logResponse('Peppol', "GET /documents/{$documentId}/status", $response->status(), $responseData);

            return $responseData;
        } catch (RequestException $e) {
            $this->logError('Request', 'GET', "/documents/{$documentId}/status", $e->getMessage(), [
                'document_id' => $documentId,
            ]);

            throw $e;
        }
    }

    /**
     * Cancel a Peppol document transmission.
     *
     * Attempts to cancel a document that hasn't been delivered yet.
     *
     * @param string $documentId The Peppol document ID
     *
     * @return bool True if cancellation was successful
     *
     * @throws RequestException If the API request fails
     */
    public function cancelDocument(string $documentId): bool
    {
        $this->logRequest('Peppol', "DELETE /documents/{$documentId}", [
            'document_id' => $documentId,
        ]);

        try {
            $response = $this->documentsClient->cancelDocument($documentId);
            $success  = $response->successful();

            $this->logResponse('Peppol', "DELETE /documents/{$documentId}", $response->status(), [
                'success' => $success,
            ]);

            return $success;
        } catch (RequestException $e) {
            // 404 means document doesn't exist or was already cancelled - treat as success
            if ($e->response?->status() === 404) {
                $this->logResponse('Peppol', "DELETE /documents/{$documentId}", 404, [
                    'success' => true,
                    'note'    => 'Document not found or already cancelled',
                ]);

                return true;
            }

            $this->logError('Request', 'DELETE', "/documents/{$documentId}", $e->getMessage(), [
                'document_id' => $documentId,
            ]);

            throw $e;
        }
    }

    /**
     * Validate that an invoice is ready for Peppol transmission.
     *
     * @param Invoice $invoice The invoice to validate
     *
     * @return void
     *
     * @throws InvalidArgumentException If validation fails
     */
    protected function validateInvoice(Invoice $invoice): void
    {
        if ( ! $invoice->customer) {
            throw new InvalidArgumentException('Invoice must have a customer');
        }

        if ( ! $invoice->invoice_number) {
            throw new InvalidArgumentException('Invoice must have an invoice number');
        }

        if ($invoice->invoiceItems->isEmpty()) {
            throw new InvalidArgumentException('Invoice must have at least one item');
        }

        // Add more validation as needed for Peppol requirements
    }

    /**
     * Prepare invoice data for Peppol transmission.
     *
     * Converts the invoice model to the format required by the Peppol API.
     *
     * @param Invoice              $invoice        The invoice to prepare
     * @param array<string, mixed> $additionalData Optional additional data
     *
     * @return array<string, mixed> Document data ready for API submission
     */
    protected function prepareDocumentData(Invoice $invoice, array $additionalData = []): array
    {
        $customer = $invoice->customer;

        // Prepare document according to Peppol UBL format
        // This is a simplified example - real implementation should follow UBL 2.1 standard
        $documentData = [
            'document_type'  => 'invoice',
            'invoice_number' => $invoice->invoice_number,
            'issue_date'     => $invoice->invoiced_at->format('Y-m-d'),
            'due_date'       => $invoice->invoice_due_at->format('Y-m-d'),
            'currency_code'  => 'EUR', // Should be configurable

            // Supplier (seller) information
            'supplier' => [
                'name' => config('app.name'),
                // Add more supplier details from company settings
            ],

            // Customer (buyer) information
            'customer' => [
                'name'            => $customer->company_name ?? $customer->customer_name,
                'endpoint_id'     => $additionalData['customer_peppol_id'] ?? null,
                'endpoint_scheme' => 'BE:CBE', // Should be configurable based on country
            ],

            // Line items
            'invoice_lines' => $invoice->invoiceItems->map(function ($item) {
                return [
                    'id'                    => $item->id,
                    'quantity'              => $item->quantity,
                    'unit_code'             => 'C62', // Default to 'unit', should be configurable
                    'line_extension_amount' => $item->subtotal,
                    'price_amount'          => $item->price,
                    'item'                  => [
                        'name'        => $item->item_name,
                        'description' => $item->description,
                    ],
                    'tax_percent' => 0, // Calculate from tax rates
                ];
            })->toArray(),

            // Monetary totals
            'legal_monetary_total' => [
                'line_extension_amount' => $invoice->invoice_item_subtotal,
                'tax_exclusive_amount'  => $invoice->invoice_item_subtotal,
                'tax_inclusive_amount'  => $invoice->invoice_total,
                'payable_amount'        => $invoice->invoice_total,
            ],

            // Tax totals
            'tax_total' => [
                'tax_amount' => $invoice->invoice_tax_total,
            ],
        ];

        // Merge with any additional data provided
        return array_merge($documentData, $additionalData);
    }
}
