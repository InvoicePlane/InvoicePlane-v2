<?php

namespace Modules\Invoices\Peppol\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;

/**
 * PeppolService - Service for managing Peppol document transmission.
 *
 * This service handles the business logic for sending invoices through the
 * Peppol network. It coordinates between the invoice data, the Peppol client,
 * and provides a clean interface for the application to interact with Peppol.
 *
 * @package Modules\Invoices\Peppol\Services
 */
class PeppolService
{
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
     * This method takes an invoice, prepares it for transmission, and sends it
     * through the Peppol network via the configured provider.
     *
     * @param Invoice $invoice The invoice to send
     * @param array<string, mixed> $additionalData Optional additional data for the transmission
     * @return array<string, mixed> Response data including document ID and status
     *
     * @throws RequestException If the Peppol API request fails
     * @throws \InvalidArgumentException If the invoice data is invalid
     */
    public function sendInvoiceToPeppol(Invoice $invoice, array $additionalData = []): array
    {
        // Validate invoice before sending
        $this->validateInvoice($invoice);

        // Prepare document data for Peppol
        $documentData = $this->prepareDocumentData($invoice, $additionalData);

        Log::info('Sending invoice to Peppol', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);

        try {
            $response = $this->documentsClient->submitDocument($documentData);

            $responseData = $response->json();

            Log::info('Invoice sent to Peppol successfully', [
                'invoice_id' => $invoice->id,
                'document_id' => $responseData['document_id'] ?? null,
            ]);

            return [
                'success' => true,
                'document_id' => $responseData['document_id'] ?? null,
                'status' => $responseData['status'] ?? 'submitted',
                'message' => 'Invoice successfully submitted to Peppol network',
                'response' => $responseData,
            ];
        } catch (RequestException $e) {
            Log::error('Failed to send invoice to Peppol', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'response' => $e->response?->json(),
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
     * @return array<string, mixed> Status information
     *
     * @throws RequestException If the API request fails
     */
    public function getDocumentStatus(string $documentId): array
    {
        $response = $this->documentsClient->getDocumentStatus($documentId);

        return $response->json();
    }

    /**
     * Cancel a Peppol document transmission.
     *
     * Attempts to cancel a document that hasn't been delivered yet.
     *
     * @param string $documentId The Peppol document ID
     * @return bool True if cancellation was successful
     *
     * @throws RequestException If the API request fails
     */
    public function cancelDocument(string $documentId): bool
    {
        $response = $this->documentsClient->cancelDocument($documentId);

        return $response->successful();
    }

    /**
     * Validate that an invoice is ready for Peppol transmission.
     *
     * @param Invoice $invoice The invoice to validate
     * @return void
     *
     * @throws \InvalidArgumentException If validation fails
     */
    protected function validateInvoice(Invoice $invoice): void
    {
        if (!$invoice->customer) {
            throw new \InvalidArgumentException('Invoice must have a customer');
        }

        if (!$invoice->invoice_number) {
            throw new \InvalidArgumentException('Invoice must have an invoice number');
        }

        if ($invoice->invoiceItems->isEmpty()) {
            throw new \InvalidArgumentException('Invoice must have at least one item');
        }

        // Add more validation as needed for Peppol requirements
    }

    /**
     * Prepare invoice data for Peppol transmission.
     *
     * Converts the invoice model to the format required by the Peppol API.
     *
     * @param Invoice $invoice The invoice to prepare
     * @param array<string, mixed> $additionalData Optional additional data
     * @return array<string, mixed> Document data ready for API submission
     */
    protected function prepareDocumentData(Invoice $invoice, array $additionalData = []): array
    {
        $customer = $invoice->customer;

        // Prepare document according to Peppol UBL format
        // This is a simplified example - real implementation should follow UBL 2.1 standard
        $documentData = [
            'document_type' => 'invoice',
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->invoiced_at->format('Y-m-d'),
            'due_date' => $invoice->invoice_due_at->format('Y-m-d'),
            'currency_code' => 'EUR', // Should be configurable
            
            // Supplier (seller) information
            'supplier' => [
                'name' => config('app.name'),
                // Add more supplier details from company settings
            ],
            
            // Customer (buyer) information
            'customer' => [
                'name' => $customer->company_name ?? $customer->customer_name,
                'endpoint_id' => $additionalData['customer_peppol_id'] ?? null,
                'endpoint_scheme' => 'BE:CBE', // Should be configurable based on country
            ],
            
            // Line items
            'invoice_lines' => $invoice->invoiceItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_code' => 'C62', // Default to 'unit', should be configurable
                    'line_extension_amount' => $item->subtotal,
                    'price_amount' => $item->price,
                    'item' => [
                        'name' => $item->item_name,
                        'description' => $item->description,
                    ],
                    'tax_percent' => 0, // Calculate from tax rates
                ];
            })->toArray(),
            
            // Monetary totals
            'legal_monetary_total' => [
                'line_extension_amount' => $invoice->invoice_item_subtotal,
                'tax_exclusive_amount' => $invoice->invoice_item_subtotal,
                'tax_inclusive_amount' => $invoice->invoice_total,
                'payable_amount' => $invoice->invoice_total,
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
