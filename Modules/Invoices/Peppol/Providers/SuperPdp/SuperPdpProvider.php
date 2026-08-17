<?php

namespace Modules\Invoices\Peppol\Providers\SuperPdp;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Clients\SuperPdp\InvoicesClient;
use Modules\Invoices\Peppol\Providers\BaseProvider;
use Modules\Invoices\Services\InvoiceService;
use Modules\Core\Support\PDF\PDFFactory;

class SuperPdpProvider extends BaseProvider
{
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
}
