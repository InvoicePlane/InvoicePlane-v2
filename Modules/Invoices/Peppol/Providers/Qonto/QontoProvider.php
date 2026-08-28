<?php

namespace Modules\Invoices\Peppol\Providers\Qonto;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Clients\Qonto\ClientInvoicesClient;
use Modules\Invoices\Peppol\Clients\Qonto\QontoClient;
use Modules\Invoices\Peppol\Clients\Qonto\SupplierInvoicesClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;
use Modules\Invoices\Services\InvoiceService;
use Modules\Core\Support\PDF\PDFFactory;

class QontoProvider extends BaseProvider
{
    protected object $clientInvoicesClient;
    protected object $supplierInvoicesClient;

    public function __construct(
        ?PeppolIntegration $integration = null,
        ?object $clientInvoicesClient = null,
        ?object $supplierInvoicesClient = null
    ) {
        parent::__construct($integration);
        $this->clientInvoicesClient = $clientInvoicesClient ?? app(ClientInvoicesClient::class);
        $this->supplierInvoicesClient = $supplierInvoicesClient ?? app(SupplierInvoicesClient::class);
    }

    public function getProviderName(): string
    {
        return 'qonto';
    }

    public function testConnection(array $config): array
    {
        return ['ok' => true, 'message' => 'Qonto connection configured'];
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

            $response = $this->clientInvoicesClient->import($pdfBinary);
            if (!$response->successful()) {
                return ['accepted' => false, 'external_id' => null, 'status_code' => $response->status(), 'message' => 'Qonto import failed', 'response' => $response->json()];
            }

            $invoiceId = $response->json('id');
            $sendResponse = $this->clientInvoicesClient->sendByEinvoice($invoiceId);

            return [
                'accepted'    => $sendResponse->successful(),
                'external_id' => $invoiceId,
                'status_code' => $sendResponse->status(),
                'message'     => 'Invoice submitted to Qonto',
                'response'    => $sendResponse->json(),
            ];
        } catch (\Throwable $e) {
            return ['accepted' => false, 'external_id' => null, 'status_code' => 0, 'message' => 'Qonto error: ' . $e->getMessage(), 'response' => null];
        }
    }

    public function getTransmissionStatus(string $externalId): array
    {
        try {
            $response = $this->clientInvoicesClient->getStatus($externalId);
            return ['status' => $response->json('status', 'unknown'), 'ack_payload' => $response->json()];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'ack_payload' => ['error' => $e->getMessage()]];
        }
    }

    public function fetchAcknowledgements(?\Carbon\Carbon $since = null): array
    {
        try {
            $filters = $since ? ['since' => $since->toIso8601String()] : [];
            $response = $this->supplierInvoicesClient->list($filters);
            return $response->json('invoices', []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function cancelDocument(string $externalId): array
    {
        return ['success' => false, 'message' => 'Qonto does not support document cancellation'];
    }

    protected function getDefaultBaseUrl(): string
    {
        return 'https://thirdparty.qonto.com/api';
    }

    public function getAccessToken(): ?string
    {
        return $this->config['access_token'] ?? null;
    }

    public function getStagingToken(): ?string
    {
        return $this->config['staging_token'] ?? null;
    }

    /**
     * Get the declarative settings schema for Qonto.
     *
     * Delegates to the client's static method for a single source of truth.
     *
     * @return array<string, array> map of config key => settings metadata
     */
    public static function settings(): array
    {
        return QontoClient::settings();
    }
}
