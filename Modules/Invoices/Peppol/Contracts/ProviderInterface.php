<?php

namespace Modules\Invoices\Peppol\Contracts;

use Illuminate\Http\Client\Response;

/**
 * Provider interface that all Peppol providers must implement
 * 
 * This ensures consistent behavior across different Peppol access point providers
 * like Storecove, e-invoice.be, Peppol Connect, etc.
 */
interface ProviderInterface
{
    /**
     * Test the connection with provider credentials
     *
     * @param array $config Provider-specific configuration
     * @return array{ok: bool, message: string}
     */
    public function testConnection(array $config): array;

    /**
     * Validate a Peppol participant ID
     *
     * @param string $scheme Peppol scheme (e.g., BE:CBE, DE:VAT)
     * @param string $id Participant identifier
     * @return array{present: bool, details: array|null}
     */
    public function validatePeppolId(string $scheme, string $id): array;

    /**
     * Send an invoice to the Peppol network
     *
     * @param array $transmissionData Data transfer object containing invoice data
     * @return array{accepted: bool, external_id: string|null, status_code: int, message: string, response: array|null}
     */
    public function sendInvoice(array $transmissionData): array;

    /**
     * Get the status of a transmission
     *
     * @param string $externalId Provider's transaction/document ID
     * @return array{status: string, ack_payload: array|null}
     */
    public function getTransmissionStatus(string $externalId): array;

    /**
     * Register a webhook callback URL (optional - not all providers support this)
     *
     * @param string $url Webhook endpoint URL
     * @param string $secret Webhook signing secret
     * @return array{success: bool, message: string}
     */
    public function registerWebhookCallback(string $url, string $secret): array;

    /**
     * Fetch acknowledgements from provider (for providers that don't support webhooks)
     *
     * @param \Carbon\Carbon|null $since Fetch acknowledgements since this timestamp
     * @return array List of acknowledgements
     */
    public function fetchAcknowledgements(?\Carbon\Carbon $since = null): array;

    /**
     * Cancel a pending or sent document
     *
     * @param string $externalId Provider's transaction/document ID
     * @return array{success: bool, message: string}
     */
    public function cancelDocument(string $externalId): array;

    /**
     * Classify an error response from the provider
     *
     * Maps provider-specific error codes to generic categories:
     * - TRANSIENT: Retryable errors (5xx, timeouts, rate limits)
     * - PERMANENT: Non-retryable errors (invalid data, unauthorized, not found)
     * - UNKNOWN: Ambiguous errors that need investigation
     *
     * @param int $statusCode HTTP status code
     * @param array|null $responseBody Response body from provider
     * @return string ERROR_TRANSIENT, ERROR_PERMANENT, or ERROR_UNKNOWN
     */
    public function classifyError(int $statusCode, ?array $responseBody = null): string;

    /**
     * Get provider name
     *
     * @return string
     */
    public function getProviderName(): string;
}
