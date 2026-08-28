<?php

namespace Modules\Invoices\Peppol\Contracts;

use Illuminate\Http\Client\Response;

/**
 * Provider interface that all Peppol providers must implement.
 *
 * This ensures consistent behavior across different Peppol access point providers
 * like Storecove, e-invoice.be, Peppol Connect, etc.
 */
interface ProviderInterface
{
    /**
     * Test the connection with provider credentials.
     *
     * @param array $config Provider-specific configuration
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(array $config): array;

    /**
     * Validate a Peppol participant ID.
     *
     * @param string $scheme Peppol scheme (e.g., BE:CBE, DE:VAT)
     * @param string $id     Participant identifier
     *
     * @return array{present: bool, details: array|null}
     */
    public function validatePeppolId(string $scheme, string $id): array;

    /**
     * Transmit an invoice to the Peppol network.
     *
     * @param array $transmissionData data transfer object containing the invoice payload, recipient identifiers, and transmission metadata
     *
     * @return array{accepted: bool, external_id: string|null, status_code: int, message: string, response: array|null} associative array with keys: `accepted` is `true` if the provider accepted the submission, `external_id` the provider's transaction/document ID or `null`, `status_code` the provider HTTP/status code, `message` a human-readable status, and `response` the raw provider response or `null`
     */
    public function sendInvoice(array $transmissionData): array;

    /**
     * Get the status of a transmission.
     *
     * @param string $externalId Provider's transaction/document ID
     *
     * @return array{status: string, ack_payload: array|null}
     */
    public function getTransmissionStatus(string $externalId): array;

    /**
     * Register or update a webhook callback URL with the provider (optional — not all providers support this).
     *
     * @param string $url    the webhook endpoint URL to register
     * @param string $secret the webhook signing secret used to verify callbacks
     *
     * @return array{success: bool, message: string} `success` is `true` if registration succeeded, `false` otherwise; `message` contains a human-readable result or error
     */
    public function registerWebhookCallback(string $url, string $secret): array;

    /**
     * Retrieve acknowledgements from the provider for polling-based integrations.
     *
     * @param \Carbon\Carbon|null $since optional timestamp to limit results to acknowledgements received at or after this time
     *
     * @return array an array of acknowledgement records; each record is an associative array representing the provider's acknowledgement payload
     */
    public function fetchAcknowledgements(?\Carbon\Carbon $since = null): array;

    /**
     * Cancel a pending or sent document identified by the provider's external ID.
     *
     * @param string $externalId provider's transaction or document ID
     *
     * @return array{success: bool, message: string} `success` is true when the cancellation was accepted, `message` contains provider response or error details
     */
    public function cancelDocument(string $externalId): array;

    /**
     * Authenticate the provider using stored credentials.
     *
     * Implementation varies by provider: OAuth2 clients refresh an access token, static-credential
     * providers validate that required keys are present.
     *
     * @return bool true if authentication succeeded or credentials are valid, false otherwise
     */
    public function authenticate(): bool;

    /**
     * Get the list of configuration keys this provider requires from merchant_clients.
     *
     * Each key is retrieved from the merchant_clients table, scoped by company_id and provider name.
     *
     * @return array<string> list of config keys (e.g., ['api_key'], ['client_id', 'client_secret', 'access_token'])
     */
    public static function settings(): array;

    /**
     * Classify a provider error into a generic category.
     *
     * Maps provider responses to one of three categories to guide retry or handling:
     * - `ERROR_TRANSIENT`: retryable conditions such as server errors, timeouts, or rate limits.
     * - `ERROR_PERMANENT`: non-retryable conditions such as invalid data, unauthorized, or not found.
     * - `ERROR_UNKNOWN`: ambiguous or unclassified conditions that require investigation.
     *
     * @param int        $statusCode   HTTP status code returned by the provider
     * @param array|null $responseBody optional response body returned by the provider to aid classification
     *
     * @return string `ERROR_TRANSIENT` if the error is retryable, `ERROR_PERMANENT` if it is not retryable, `ERROR_UNKNOWN` otherwise
     */
    public function classifyError(int $statusCode, ?array $responseBody = null): string;

    /**
     * Retrieve the provider's canonical name.
     *
     * @return string The provider's identifier (human-readable name, e.g. "Storecove").
     */
    public function getProviderName(): string;
}
