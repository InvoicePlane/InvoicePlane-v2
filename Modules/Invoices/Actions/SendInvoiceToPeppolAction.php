<?php

namespace Modules\Invoices\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Client\RequestException;
use InvalidArgumentException;
use Modules\Core\Models\User;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Services\PeppolService;

/**
 * SendInvoiceToPeppolAction - Action for sending invoices to Peppol network.
 *
 * This action handles the process of gathering invoice information and
 * sending it to the Peppol network through the PeppolService. It provides
 * a clean interface for both the EditInvoice page and the ListInvoices table.
 */
class SendInvoiceToPeppolAction
{
    /**
     * The Peppol service instance.
     *
     * @var PeppolService
     */
    protected PeppolService $peppolService;

    /**
     * Constructor.
     *
     * @param PeppolService $peppolService The Peppol service
     */
    public function __construct(PeppolService $peppolService)
    {
        $this->peppolService = $peppolService;
    }

    /**
     * Execute the action to send an invoice to Peppol.
     *
     * This method gathers all necessary information from the invoice and
     * submits it to the Peppol network. It returns the result of the operation.
     *
     * @param Invoice              $invoice        The invoice to send
     * @param array<string, mixed> $additionalData Optional additional data (e.g., Peppol ID)
     * @param User|null            $user           Optional user for authorization check; if null, uses current auth user
     *
     * @return array<string, mixed> The result of the operation
     *
     * @throws RequestException         If the Peppol API request fails
     * @throws InvalidArgumentException If the invoice data is invalid
     * @throws AuthorizationException   If user is not authorized to edit the invoice
     */
    public function execute(Invoice $invoice, array $additionalData = [], ?User $user = null): array
    {
        $user = $user ?? auth()->user();

        // Verify user can edit this invoice
        if ($user && ! $user->can('update', $invoice)) {
            throw new AuthorizationException('You are not authorized to send this invoice to Peppol');
        }

        // Load necessary relationships
        $invoice->load(['customer', 'invoiceItems']);

        // Validate that invoice is in a state that can be sent
        $this->validateInvoiceState($invoice);

        // Send to Peppol
        $result = $this->peppolService->sendInvoiceToPeppol($invoice, $additionalData);

        // Optionally, you could update the invoice record here
        // to track that it was sent to Peppol (e.g., add a peppol_document_id field)
        // $invoice->update(['peppol_document_id' => $result['document_id']]);

        return $result;
    }

    /**
     * Get the status of a previously sent invoice from Peppol.
     *
     * @param string $documentId The Peppol document ID
     *
     * @return array<string, mixed> Status information
     *
     * @throws RequestException If the API request fails
     */
    public function getStatus(string $documentId): array
    {
        return $this->peppolService->getDocumentStatus($documentId);
    }

    /**
     * Cancel a Peppol document transmission.
     *
     * @param string $documentId The Peppol document ID
     *
     * @return bool True if cancellation was successful
     *
     * @throws RequestException If the API request fails
     */
    public function cancel(string $documentId): bool
    {
        return $this->peppolService->cancelDocument($documentId);
    }

    /**
     * Validate that the invoice is in a valid state for Peppol transmission.
     *
     * @param Invoice $invoice The invoice to validate
     *
     * @return void
     *
     * @throws InvalidArgumentException If validation fails
     */
    protected function validateInvoiceState(Invoice $invoice): void
    {
        /* Check if invoice is in draft status - drafts should not be sent */
        if ($invoice->invoice_status?->value === 'draft' || $invoice->invoice_status === \Modules\Invoices\Enums\InvoiceStatus::DRAFT) {
            throw new InvalidArgumentException('Cannot send draft invoices to Peppol');
        }

        /* Additional business logic validation can be added here */
    }
}
