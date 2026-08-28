<?php

namespace Modules\Invoices\Peppol\Providers\SuperPdp;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Clients\SuperPdp\InvoicesClient;
use Modules\Invoices\Peppol\Clients\SuperPdp\SuperPdpClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;
use Modules\Invoices\Peppol\Providers\Concerns\RefreshesOAuth2Token;
use Modules\Invoices\Services\InvoiceService;
use Modules\Core\Support\PDF\PDFFactory;

class SuperPdpProvider extends BaseProvider
{
    use RefreshesOAuth2Token;
    protected object $invoicesClient;

    public function __construct(
        ?PeppolIntegration $integration = null,
        ?object $invoicesClient = null
    ) {
        parent::__construct($integration);
        $this->invoicesClient = $invoicesClient ?? app(InvoicesClient::class);
    }

    public function getProviderName(): string
    {
        return 'super_pdp';
    }

    public function testConnection(array $config): array
    {
        return ['ok' => true, 'message' => 'SuperPDP connection configured'];
    }

    public function validatePeppolId(string $scheme, string $id): array
    {
        return ['present' => true, 'details' => ['scheme' => $scheme, 'identifier' => $id]];
    }

    public function sendInvoice(array $transmissionData): array
    {
        try {
            $invoice = $transmissionData['invoice'] ?? null;
            if (!$invoice) {
                return ['accepted' => false, 'external_id' => null, 'status_code' => 0, 'message' => 'Missing invoice', 'response' => null];
            }

            $html = app(InvoiceService::class)->renderHtml($invoice);
            $pdfBinary = PDFFactory::create()->getOutput($html);

            $response = $this->invoicesClient->sendInvoice($pdfBinary, [
                'recipient' => $transmissionData['recipient_id'] ?? '',
                'scheme' => $transmissionData['recipient_scheme'] ?? '',
            ]);

            if (!$response->successful()) {
                return ['accepted' => false, 'external_id' => null, 'status_code' => $response->status(), 'message' => 'SuperPDP rejected submission', 'response' => $response->json()];
            }

            return ['accepted' => true, 'external_id' => $response->json('externalId'), 'status_code' => $response->status(), 'message' => 'PDF submitted to SuperPDP', 'response' => $response->json()];
        } catch (\Throwable $e) {
            return ['accepted' => false, 'external_id' => null, 'status_code' => 0, 'message' => 'SuperPDP error: ' . $e->getMessage(), 'response' => null];
        }
    }

    public function getTransmissionStatus(string $externalId): array
    {
        try {
            $response = $this->invoicesClient->getInvoiceStatus($externalId);
            return ['status' => $response->json('status', 'unknown'), 'ack_payload' => $response->json()];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'ack_payload' => ['error' => $e->getMessage()]];
        }
    }

    public function cancelDocument(string $externalId): array
    {
        return ['success' => false, 'message' => 'SuperPDP does not support document cancellation'];
    }

    protected function getDefaultBaseUrl(): string
    {
        return 'https://api.superpdp.com/v1';
    }

    public function getClientId(): ?string
    {
        return $this->config['client_id'] ?? null;
    }

    public function getClientSecret(): ?string
    {
        return $this->config['client_secret'] ?? null;
    }

    public function getAccessToken(): ?string
    {
        return $this->config['access_token'] ?? null;
    }

    /**
     * Get the declarative settings schema for SuperPDP OAuth2.
     *
     * Delegates to the client's static method for a single source of truth.
     *
     * @return array<string, array> map of config key => settings metadata
     */
    public static function settings(): array
    {
        return SuperPdpClient::settings();
    }

    /**
     * Authenticate with SuperPDP using OAuth2 client-credentials flow.
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
     * Get the OAuth2 client class for SuperPDP.
     *
     * @return string the full class name
     */
    protected function getOAuth2ClientClass(): ?string
    {
        return SuperPdpClient::class;
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
        $this->invoicesClient->setAccessToken($token);
    }
}
