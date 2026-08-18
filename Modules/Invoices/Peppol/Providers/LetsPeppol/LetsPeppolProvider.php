<?php

namespace Modules\Invoices\Peppol\Providers\LetsPeppol;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Clients\LetsPeppol\CreditNoteClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\DocumentClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\InvoiceClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\LetsPeppolClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\ParticipantClient;
use Modules\Invoices\Peppol\Clients\LetsPeppol\TransmissionClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;
use Modules\Invoices\Peppol\Providers\Concerns\RefreshesOAuth2Token;

/**
 * LetsPeppolProvider - LetsPeppol OAuth2 Peppol provider.
 */
class LetsPeppolProvider extends BaseProvider
{
    use RefreshesOAuth2Token;
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

        if ($invoiceClient) {
            $this->invoiceClient = $invoiceClient;
        } else {
            $this->invoiceClient = new InvoiceClient(
                app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
                $this->getAccessToken() ?? '',
                $this->getDefaultBaseUrl()
            );
        }

        if ($creditNoteClient) {
            $this->creditNoteClient = $creditNoteClient;
        } else {
            $this->creditNoteClient = new CreditNoteClient(
                app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
                $this->getAccessToken() ?? '',
                $this->getDefaultBaseUrl()
            );
        }

        if ($participantClient) {
            $this->participantClient = $participantClient;
        } else {
            $this->participantClient = new ParticipantClient(
                app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
                $this->getAccessToken() ?? '',
                $this->getDefaultBaseUrl()
            );
        }

        if ($transmissionClient) {
            $this->transmissionClient = $transmissionClient;
        } else {
            $this->transmissionClient = new TransmissionClient(
                app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
                $this->getAccessToken() ?? '',
                $this->getDefaultBaseUrl()
            );
        }

        if ($documentClient) {
            $this->documentClient = $documentClient;
        } else {
            $this->documentClient = new DocumentClient(
                app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
                $this->getAccessToken() ?? '',
                $this->getDefaultBaseUrl()
            );
        }
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

    /**
     * Get the OAuth2 client ID from integration configuration.
     *
     * @return string|null The client ID, or null if not configured
     */
    public function getClientId(): ?string
    {
        return $this->config['client_id'] ?? null;
    }

    /**
     * Get the OAuth2 client secret from integration configuration.
     *
     * @return string|null The client secret, or null if not configured
     */
    public function getClientSecret(): ?string
    {
        return $this->config['client_secret'] ?? null;
    }

    /**
     * Get the stored OAuth2 access token from integration configuration.
     *
     * @return string|null The access token, or null if not yet obtained
     */
    public function getAccessToken(): ?string
    {
        return $this->config['access_token'] ?? null;
    }

    /**
     * Authenticate with LetsPeppol using OAuth2 client-credentials flow.
     *
     * Ensures a valid access token exists, fetching and persisting a new one if needed.
     * Tokens are stored in merchant_clients with expiry tracking for automatic refresh.
     *
     * @return bool True if authentication succeeded and token is valid
     */
    public function authenticate(): bool
    {
        return $this->ensureAuthenticated();
    }

    /**
     * Get the declarative settings schema for LetsPeppol OAuth2.
     *
     * Delegates to the client's static method for a single source of truth.
     *
     * @return array<string, array> map of config key => settings metadata
     */
    public static function settings(): array
    {
        return LetsPeppolClient::settings();
    }

    /**
     * Get the OAuth2 client class for LetsPeppol.
     *
     * @return string the full class name
     */
    protected function getOAuth2ClientClass(): ?string
    {
        return LetsPeppolClient::class;
    }

    /**
     * Propagate the access token to all resource clients.
     *
     * Called after token refresh to ensure all clients use the new token.
     *
     * @param string $token the new access token
     */
    protected function propagateAccessToken(string $token): void
    {
        $this->invoiceClient->setAccessToken($token);
        $this->creditNoteClient->setAccessToken($token);
        $this->participantClient->setAccessToken($token);
        $this->transmissionClient->setAccessToken($token);
        $this->documentClient->setAccessToken($token);
    }
}
