<?php

namespace Modules\Invoices\Peppol\Providers\Storecove;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Clients\Storecove\DocumentSubmissionsClient;
use Modules\Invoices\Peppol\Clients\Storecove\ReceivedDocumentsClient;
use Modules\Invoices\Peppol\Clients\Storecove\StorecoveClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;

/**
 * StorecoveProvider - Storecove Peppol provider implementation.
 *
 * Integrates with Storecove's Peppol network, supporting document submission,
 * evidence retrieval, and transmission status tracking.
 */
class StorecoveProvider extends BaseProvider
{
    protected DocumentSubmissionsClient $documentSubmissionsClient;
    protected ReceivedDocumentsClient $receivedDocumentsClient;

    public function __construct(
        ?PeppolIntegration $integration = null,
        ?DocumentSubmissionsClient $documentSubmissionsClient = null,
        ?ReceivedDocumentsClient $receivedDocumentsClient = null
    ) {
        parent::__construct($integration);

        if ($documentSubmissionsClient) {
            $this->documentSubmissionsClient = $documentSubmissionsClient;
        } else {
            $this->documentSubmissionsClient = new DocumentSubmissionsClient(
                app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
                $this->getApiKey() ?? '',
                $this->getDefaultBaseUrl()
            );
        }

        if ($receivedDocumentsClient) {
            $this->receivedDocumentsClient = $receivedDocumentsClient;
        } else {
            $this->receivedDocumentsClient = new ReceivedDocumentsClient(
                app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
                $this->getApiKey() ?? '',
                $this->getDefaultBaseUrl()
            );
        }
    }

    public function getProviderName(): string
    {
        return 'storecove';
    }

    public function testConnection(array $config): array
    {
        try {
            // Call a real endpoint with a non-existent GUID
            // 404 means API key was accepted (endpoint reachable but resource not found)
            // 401/403 means authentication failed
            // Any 2xx means success
            $response = $this->documentSubmissionsClient->getEvidence('connectivity-check', 'sending');

            if ($response->status() === 404) {
                // 404 on a valid endpoint = API key worked, just invalid GUID (expected)
                return [
                    'ok'      => true,
                    'message' => 'Connection successful',
                ];
            }

            if ($response->status() === 401 || $response->status() === 403) {
                return [
                    'ok'      => false,
                    'message' => 'Invalid API key or unauthorized',
                ];
            }

            if ($response->successful()) {
                return [
                    'ok'      => true,
                    'message' => 'Connection successful',
                ];
            }

            return [
                'ok'      => false,
                'message' => 'Connection failed: ' . ($response->body() ?: 'HTTP ' . $response->status()),
            ];
        } catch (\Throwable $e) {
            return [
                'ok'      => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function validatePeppolId(string $scheme, string $id): array
    {
        return [
            'present' => true,
            'details' => [
                'scheme'     => $scheme,
                'identifier' => $id,
                'note'       => 'Storecove accepts all valid Peppol identifiers',
            ],
        ];
    }

    public function sendInvoice(array $transmissionData): array
    {
        try {
            $xml = $transmissionData['xml'] ?? '';
            $recipientScheme = $transmissionData['recipient_scheme'] ?? '';
            $recipientId = $transmissionData['recipient_id'] ?? '';
            $legalEntityId = $this->getLegalEntityId();

            $payload = [
                'legalEntityId' => (int) $legalEntityId,
                'document'      => [
                    'rawDocumentData' => [
                        'document'       => base64_encode($xml),
                        'documentType'   => 'invoice',
                        'parseStrategy'  => 'ubl',
                    ],
                ],
                'routing' => [
                    'eIdentifiers' => [
                        [
                            'scheme' => $recipientScheme,
                            'id'     => $recipientId,
                        ],
                    ],
                ],
            ];

            $response = $this->documentSubmissionsClient->submitDocument($payload);

            if (!$response->successful()) {
                return [
                    'accepted'    => false,
                    'external_id' => null,
                    'status_code' => $response->status(),
                    'message'     => 'Storecove rejected submission',
                    'response'    => $response->json(),
                ];
            }

            $guid = $response->json('entity.guid');

            return [
                'accepted'    => true,
                'external_id' => $guid,
                'status_code' => $response->status(),
                'message'     => 'Document submitted to Storecove',
                'response'    => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'accepted'    => false,
                'external_id' => null,
                'status_code' => 0,
                'message'     => 'Storecove submission error: ' . $e->getMessage(),
                'response'    => null,
            ];
        }
    }

    public function getTransmissionStatus(string $externalId): array
    {
        try {
            $response = $this->documentSubmissionsClient->getEvidence($externalId, 'sending');

            if (!$response->successful()) {
                return [
                    'status'      => 'error',
                    'ack_payload' => ['error' => 'Failed to retrieve transmission status'],
                ];
            }

            $status = $response->json('status', 'unknown');

            return [
                'status'      => $status,
                'ack_payload' => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'status'      => 'error',
                'ack_payload' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function cancelDocument(string $externalId): array
    {
        return [
            'success' => false,
            'message' => 'Storecove does not support document cancellation',
        ];
    }

    protected function getDefaultBaseUrl(): string
    {
        return 'https://api.storecove.com/api/v2';
    }

    public function getApiKey(): ?string
    {
        return $this->config['api_key'] ?? null;
    }

    public function getLegalEntityId(): ?string
    {
        return $this->config['legal_entity_id'] ?? null;
    }

    /**
     * Get the declarative settings schema for Storecove.
     *
     * Delegates to the client's static method for a single source of truth.
     *
     * @return array<string, array> map of config key => settings metadata
     */
    public static function settings(): array
    {
        return StorecoveClient::settings();
    }
}
