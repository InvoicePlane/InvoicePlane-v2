<?php

namespace Modules\Invoices\Peppol\Providers\EInvoiceBe;

use Carbon\Carbon;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\HealthClient;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\ParticipantsClient;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\TrackingClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;

/**
 * e-invoice.be Peppol provider implementation
 */
class EInvoiceBeProvider extends BaseProvider
{
    protected DocumentsClient $documentsClient;
    protected ParticipantsClient $participantsClient;
    protected TrackingClient $trackingClient;
    protected HealthClient $healthClient;

    public function __construct(
        ?object $integration = null,
        ?DocumentsClient $documentsClient = null,
        ?ParticipantsClient $participantsClient = null,
        ?TrackingClient $trackingClient = null,
        ?HealthClient $healthClient = null
    ) {
        parent::__construct($integration);
        
        $this->documentsClient = $documentsClient ?? app(DocumentsClient::class);
        $this->participantsClient = $participantsClient ?? app(ParticipantsClient::class);
        $this->trackingClient = $trackingClient ?? app(TrackingClient::class);
        $this->healthClient = $healthClient ?? app(HealthClient::class);
    }

    public function getProviderName(): string
    {
        return 'e_invoice_be';
    }

    protected function getDefaultBaseUrl(): string
    {
        return 'https://api.e-invoice.be';
    }

    public function testConnection(array $config): array
    {
        try {
            $response = $this->healthClient->ping();
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'ok' => true,
                    'message' => 'Connection successful. API is reachable.',
                ];
            }

            return [
                'ok' => false,
                'message' => "Connection failed with status: {$response->status()}",
            ];
        } catch (\Exception $e) {
            $this->logPeppolError('e-invoice.be connection test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'ok' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }

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
        } catch (\Exception $e) {
            $this->logPeppolError('Peppol ID validation failed', [
                'scheme' => $scheme,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'present' => false,
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function sendInvoice(array $transmissionData): array
    {
        try {
            $response = $this->documentsClient->submitDocument($transmissionData);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'accepted' => true,
                    'external_id' => $data['document_id'] ?? $data['id'] ?? null,
                    'status_code' => $response->status(),
                    'message' => 'Document submitted successfully',
                    'response' => $data,
                ];
            }

            return [
                'accepted' => false,
                'external_id' => null,
                'status_code' => $response->status(),
                'message' => $response->body(),
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            $this->logPeppolError('Invoice submission to e-invoice.be failed', [
                'invoice_id' => $transmissionData['invoice_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'accepted' => false,
                'external_id' => null,
                'status_code' => 0,
                'message' => $e->getMessage(),
                'response' => null,
            ];
        }
    }

    public function getTransmissionStatus(string $externalId): array
    {
        try {
            $response = $this->trackingClient->getStatus($externalId);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'status' => $data['status'] ?? 'unknown',
                    'ack_payload' => $data,
                ];
            }

            return [
                'status' => 'error',
                'ack_payload' => null,
            ];
        } catch (\Exception $e) {
            $this->logPeppolError('Status check failed for e-invoice.be', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'ack_payload' => ['error' => $e->getMessage()],
            ];
        }
    }

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
        } catch (\Exception $e) {
            $this->logPeppolError('Document cancellation failed', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch acknowledgements since a given time
     * Uses the tracking client to get recent documents and their status
     */
    public function fetchAcknowledgements(?Carbon $since = null): array
    {
        try {
            // Default to last 7 days if not specified
            $since = $since ?? Carbon::now()->subDays(7);
            
            $response = $this->trackingClient->listDocuments([
                'from_date' => $since->toIso8601String(),
            ]);
            
            if ($response->successful()) {
                return $response->json('documents', []);
            }

            return [];
        } catch (\Exception $e) {
            $this->logPeppolError('Failed to fetch acknowledgements from e-invoice.be', [
                'since' => $since,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * e-invoice.be specific error classification
     */
    public function classifyError(int $statusCode, ?array $responseBody = null): string
    {
        // Check for specific e-invoice.be error codes in response body
        if ($responseBody && isset($responseBody['error_code'])) {
            return match($responseBody['error_code']) {
                'RATE_LIMIT_EXCEEDED' => 'TRANSIENT',
                'SERVICE_UNAVAILABLE' => 'TRANSIENT',
                'INVALID_PARTICIPANT' => 'PERMANENT',
                'INVALID_DOCUMENT' => 'PERMANENT',
                'AUTHENTICATION_FAILED' => 'PERMANENT',
                default => parent::classifyError($statusCode, $responseBody),
            };
        }

        return parent::classifyError($statusCode, $responseBody);
    }
}
