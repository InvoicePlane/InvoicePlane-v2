<?php

namespace Modules\Invoices\Peppol\Clients\EInvoiceBe;

use Modules\Invoices\Peppol\Clients\BasePeppolClient;

/**
 * EInvoiceBeClient - Base client for e-invoice.be Peppol provider.
 *
 * This client provides authentication and base configuration specific to
 * the e-invoice.be Peppol provider API. It serves as the foundation for
 * all e-invoice.be endpoint clients.
 *
 * @see https://api.e-invoice.be/docs API Documentation
 * @package Modules\Invoices\Peppol\Clients\EInvoiceBe
 */
class EInvoiceBeClient extends BasePeppolClient
{
    /**
     * Get authentication headers for e-invoice.be API.
     *
     * e-invoice.be uses API key authentication via the X-API-Key header.
     *
     * @return array<string, string> Authentication headers
     */
    protected function getAuthenticationHeaders(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get the request timeout for e-invoice.be operations.
     *
    protected function getTimeout(): int
    {
        return (int) config('invoices.peppol.e_invoice_be.timeout', 90);
    }
}
