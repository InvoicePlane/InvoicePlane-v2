<?php

namespace Modules\Invoices\Peppol\Providers\EInvoiceBe;

use Carbon\Carbon;
use Exception;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\HealthClient;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\ParticipantsClient;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\TrackingClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;

/**
 * e-invoice.be Peppol provider implementation.
 */
class EInvoiceBeProvider extends BaseProvider
{
    protected DocumentsClient $documentsClient;

    protected ParticipantsClient $participantsClient;

    protected TrackingClient $trackingClient;

    protected HealthClient $healthClient;

    /**
     * Create a new EInvoiceBeProvider instance, optionally injecting integration and HTTP clients.
     *
     * @param object|null             $integration        optional integration configuration or model
     * @param DocumentsClient|null    $documentsClient    optional documents client; if omitted, the provider will resolve one from the container
     * @param ParticipantsClient|null $participantsClient optional participants client; if omitted, the provider will resolve one from the container
     * @param TrackingClient|null     $trackingClient     optional tracking client; if omitted, the provider will resolve one from the container
     * @param HealthClient|null       $healthClient       optional health client; if omitted, the provider will resolve one from the container
     */
    public function __construct(
        ?object $integration = null,
        ?DocumentsClient $documentsClient = null,
        ?ParticipantsClient $participantsClient = null,
        ?TrackingClient $trackingClient = null,
        ?HealthClient $healthClient = null
    ) {
        parent::__construct($integration);

        $this->documentsClient    = $documentsClient ?? app(DocumentsClient::class);
        $this->participantsClient = $participantsClient ?? app(ParticipantsClient::class);
        $this->trackingClient     = $trackingClient ?? app(TrackingClient::class);
        $this->healthClient       = $healthClient ?? app(HealthClient::class);
    }

    /**
     * Provider identifier for the e-invoice.be Peppol integration.
     *
     * @return string the provider identifier 'e_invoice_be'
     */
    public function getProviderName(): string
    {
        return 'e_invoice_be';
    }

    /**
     * Checks connectivity to the e-invoice.be API via the health client.
     *
     * @param array $config optional connection configuration (may include credentials or endpoint overrides)
     *
     * @return array associative array with keys: 'ok' (`true` if API reachable, `false` otherwise) and 'message' (human-readable status or error message)
     */
    public function testConnection(array $config): array
    {
        try {
            $response = $this->healthClient->ping();

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'ok'      => true,
                    'message' => 'Connection successful. API is reachable.',
                ];
            }

            return [
                'ok'      => false,
                'message' => "Connection failed with status: {$response->status()}",
            ];
        } catch (Exception $e) {
            $this->logPeppolError('e-invoice.be connection test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'ok'      => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Checks whether a Peppol participant exists for the given identifier and returns details if found.
     *
     * Performs a lookup using the participants client; a 404 response is treated as "not present".
     *
     * @param string $scheme Identifier scheme used for the lookup (e.g., "GLN", "VAT").
     * @param string $id     the participant identifier to validate
     *
     * @return array An array with keys:
     *               - `present` (bool): `true` if the participant exists, `false` otherwise.
     *               - `details` (array|null): participant data when present; `null` if not found; or an `['error' => string]` structure on failure.
     */
    public function validatePeppolId(string $scheme, string $id): array
    {
        try {
            $response = $this->participantsClient->searchParticipant($id, $scheme);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'present' => true,
                    'details' => $data,
                ];
            }

            // 404 means participant not found
            if ($response->status() === 404) {
                return [
                    'present' => false,
                    'details' => null,
                ];
            }

            // Other errors
            return [
                'present' => false,
                'details' => ['error' => $response->body()],
            ];
        } catch (Exception $e) {
            $this->logPeppolError('Peppol ID validation failed', [
                'scheme' => $scheme,
                'id'     => $id,
                'error'  => $e->getMessage(),
            ]);

            return [
                'present' => false,
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Submits an invoice document to e-invoice.be and returns the submission result.
     *
     * @param array $transmissionData the payload sent to the documents API (may include keys such as `invoice_id` used for logging)
     *
     * @return array{
     *     accepted: bool,
     *     external_id: string|null,
     *     status_code: int,
     *     message: string,
     *     response: array|null
     * }
     * @return array{
     *     accepted: bool,                // `true` if the document was accepted by the API, `false` otherwise
     *     external_id: string|null,      // provider-assigned document identifier when available
     *     status_code: int,              // HTTP status code returned by the provider (0 on exception)
     *     message: string,               // human-readable message or error body
     *     response: array|null           // parsed response body on success/failure, or null if an exception occurred
     * }
     */
    public function sendInvoice(array $transmissionData): array
    {
        try {
            $response = $this->documentsClient->submitDocument($transmissionData);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'accepted'    => true,
                    'external_id' => $data['document_id'] ?? $data['id'] ?? null,
                    'status_code' => $response->status(),
                    'message'     => 'Document submitted successfully',
                    'response'    => $data,
                ];
            }

            return [
                'accepted'    => false,
                'external_id' => null,
                'status_code' => $response->status(),
                'message'     => $response->body(),
                'response'    => $response->json(),
            ];
        } catch (Exception $e) {
            $this->logPeppolError('Invoice submission to e-invoice.be failed', [
                'invoice_id' => $transmissionData['invoice_id'] ?? null,
                'error'      => $e->getMessage(),
            ]);

            return [
                'accepted'    => false,
                'external_id' => null,
                'status_code' => 0,
                'message'     => $e->getMessage(),
                'response'    => null,
            ];
        }
    }

    /**
     * Retrieve the transmission status and acknowledgement payload for a given external document ID.
     *
     * @param string $externalId the provider's external document identifier
     *
     * @return array An associative array with keys:
     *               - `status` (string): transmission status (e.g., `'unknown'`, `'error'`, or provider-specific status).
     *               - `ack_payload` (array|null): acknowledgement payload returned by the provider, or `null` when unavailable.
     */
    public function getTransmissionStatus(string $externalId): array
    {
        try {
            $response = $this->trackingClient->getStatus($externalId);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'status'      => $data['status'] ?? 'unknown',
                    'ack_payload' => $data,
                ];
            }

            return [
                'status'      => 'error',
                'ack_payload' => null,
            ];
        } catch (Exception $e) {
            $this->logPeppolError('Status check failed for e-invoice.be', [
                'external_id' => $externalId,
                'error'       => $e->getMessage(),
            ]);

            return [
                'status'      => 'error',
                'ack_payload' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Cancel a previously submitted document identified by its external ID.
     *
     * @param string $externalId the external identifier of the document to cancel
     *
     * @return array An associative array with keys:
     *               - `success` (`bool`): `true` if cancellation succeeded, `false` otherwise.
     *               - `message` (`string`): a success message or an error/cancellation failure message.
     */
    public function cancelDocument(string $externalId): array
    {
        try {
            $response = $this->documentsClient->cancelDocument($externalId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Document cancelled successfully',
                ];
            }

            return [
                'success' => false,
                'message' => "Cancellation failed: {$response->body()}",
            ];
        } catch (Exception $e) {
            $this->logPeppolError('Document cancellation failed', [
                'external_id' => $externalId,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve acknowledgement documents from e-invoice.be since a given timestamp.
     *
     * If `$since` is null, defaults to 7 days ago. Queries the tracking client and
     * returns the `documents` array from the response or an empty array on failure.
     *
     * @param Carbon|null $since the earliest timestamp to include (ISO-8601); if null, defaults to now minus 7 days
     *
     * @return array an array of acknowledgement document payloads, or an empty array if none were found or the request failed
     */
    public function fetchAcknowledgements(?Carbon $since = null): array
    {
        try {
            // Default to last 7 days if not specified
            $since ??= Carbon::now()->subDays(7);

            $response = $this->trackingClient->listDocuments([
                'from_date' => $since->toIso8601String(),
            ]);

            if ($response->successful()) {
                return $response->json('documents', []);
            }

            return [];
        } catch (Exception $e) {
            $this->logPeppolError('Failed to fetch acknowledgements from e-invoice.be', [
                'since' => $since,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Classifies an error according to e-invoice.be-specific error codes.
     *
     * If `$responseBody` contains an `error_code`, maps known codes to either
     * `'TRANSIENT'` or `'PERMANENT'`. If no known code is present, delegates to
     * the general classification logic.
     *
     * @param int        $statusCode   HTTP status code returned by the upstream service
     * @param array|null $responseBody decoded JSON response body; may contain an `error_code` key
     *
     * @return string `'TRANSIENT'` if the error is transient, `'PERMANENT'` if permanent, otherwise the general classification result
     */
    public function classifyError(int $statusCode, ?array $responseBody = null): string
    {
        // Check for specific e-invoice.be error codes in response body
        if ($responseBody && isset($responseBody['error_code'])) {
            return match($responseBody['error_code']) {
                'RATE_LIMIT_EXCEEDED'   => 'TRANSIENT',
                'SERVICE_UNAVAILABLE'   => 'TRANSIENT',
                'INVALID_PARTICIPANT'   => 'PERMANENT',
                'INVALID_DOCUMENT'      => 'PERMANENT',
                'AUTHENTICATION_FAILED' => 'PERMANENT',
                default                 => parent::classifyError($statusCode, $responseBody),
            };
        }

        return parent::classifyError($statusCode, $responseBody);
    }

    /**
     * Get the API key from integration configuration.
     *
     * @return string|null The API key, or null if not configured
     */
    public function getApiKey(): ?string
    {
        return $this->config['api_key'] ?? null;
    }

    /**
     * Get the list of required settings/credentials for e-invoice.be.
     *
     * These fields define what configuration values must be stored in the database.
     * Base URL is hardcoded in the provider.
     *
     * @return array<string> List of setting names
     */
    public function settings(): array
    {
        return [
            'api_key',  // API key from e-invoice.be dashboard
        ];
    }

    /**
     * Provide the default base URL for the e-invoice.be API.
     *
     * @return string The default base URL for the e-invoice.be API.
     */
    protected function getDefaultBaseUrl(): string
    {
        return 'https://api.e-invoice.be';
    }
}
