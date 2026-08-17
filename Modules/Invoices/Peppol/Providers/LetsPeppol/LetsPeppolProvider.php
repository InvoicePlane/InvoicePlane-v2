<?php

namespace Modules\Invoices\Peppol\Providers\LetsPeppol;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Clients\LetsPeppol\CreditNoteClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\DocumentClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\InvoiceClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\ParticipantClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\TransmissionClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;

/**
 * LetsPeppolProvider - LetsPeppol OAuth2 Peppol provider.
 */
class LetsPeppolProvider extends BaseProvider
{
    protected object $invoiceClient;
    protected object $creditNoteClient;
    protected object $participantClient;
    protected object $transmissionClient;
    protected object $documentClient;

    public function __construct(
        ?PeppolIntegration $integration = null,
        ?object $invoiceClient = null,
        ?object $creditNoteClient = null,
        ?object $participantClient = null,
        ?object $transmissionClient = null,
        ?object $documentClient = null
    ) {
        parent::__construct($integration);

        $this->invoiceClient = $invoiceClient ?? app(InvoiceClient::class);
        $this->creditNoteClient = $creditNoteClient ?? app(CreditNoteClient::class);
        $this->participantClient = $participantClient ?? app(ParticipantClient::class);
        $this->transmissionClient = $transmissionClient ?? app(TransmissionClient::class);
        $this->documentClient = $documentClient ?? app(DocumentClient::class);
    }

    public function getProviderName(): string
    {
        return 'lets_peppol';
    }

    public function testConnection(array $config): array
    {
        return [
            'ok'      => true,
            'message' => 'LetsPeppol connection configured',
        ];
    }

    public function validatePeppolId(string $scheme, string $id): array
    {
        return [
            'present' => true,
            'details' => ['scheme' => $scheme, 'identifier' => $id],
        ];
    }

    public function sendInvoice(array $transmissionData): array
    {
        try {
            $xml = $transmissionData['xml'] ?? '';
            $recipientScheme = $transmissionData['recipient_scheme'] ?? '';
            $recipientId = $transmissionData['recipient_id'] ?? '';

            $payload = [
                'document'      => base64_encode($xml),
                'documentType'  => 'invoice',
                'recipient'     => ['scheme' => $recipientScheme, 'identifier' => $recipientId],
            ];

            $response = $this->invoiceClient->submitInvoice($payload);

            if (!$response->successful()) {
                return [
                    'accepted'    => false,
                    'external_id' => null,
                    'status_code' => $response->status(),
                    'message'     => 'LetsPeppol rejected submission',
                    'response'    => $response->json(),
                ];
            }

            $id = $response->json('id');

            return [
                'accepted'    => true,
                'external_id' => $id,
                'status_code' => $response->status(),
                'message'     => 'Document submitted to LetsPeppol',
                'response'    => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'accepted'    => false,
                'external_id' => null,
                'status_code' => 0,
                'message'     => 'LetsPeppol submission error: ' . $e->getMessage(),
                'response'    => null,
            ];
        }
    }

    public function getTransmissionStatus(string $externalId): array
    {
        try {
            $response = $this->transmissionClient->getStatus($externalId);

            if (!$response->successful()) {
                return [
                    'status'      => 'error',
                    'ack_payload' => ['error' => 'Failed to retrieve status'],
                ];
            }

            return [
                'status'      => $response->json('status', 'unknown'),
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
        try {
            $response = $this->documentClient->cancelDocument($externalId);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Document cancelled' : 'Cancellation failed',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Cancellation error: ' . $e->getMessage(),
            ];
        }
    }

    protected function getDefaultBaseUrl(): string
    {
        return 'https://api.letspeppol.com/api/v1';
    }
}
