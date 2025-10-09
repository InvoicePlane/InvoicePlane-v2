<?php

namespace Modules\Invoices\Peppol\Providers\Storecove;

use Carbon\Carbon;
use Modules\Invoices\Peppol\Providers\BaseProvider;

/**
 * Storecove Peppol provider implementation
 * 
 * TODO: Implement full Storecove API integration
 */
class StorecoveProvider extends BaseProvider
{
    public function getProviderName(): string
    {
        return 'storecove';
    }

    protected function getDefaultBaseUrl(): string
    {
        return 'https://api.storecove.com/api/v2';
    }

    public function testConnection(array $config): array
    {
        // TODO: Implement Storecove connection test
        return [
            'ok' => false,
            'message' => 'Storecove provider not yet implemented',
        ];
    }

    public function validatePeppolId(string $scheme, string $id): array
    {
        // TODO: Implement Storecove Peppol ID validation
        return [
            'present' => false,
            'details' => ['error' => 'Storecove provider not yet implemented'],
        ];
    }

    public function sendInvoice(array $transmissionData): array
    {
        // TODO: Implement Storecove invoice sending
        return [
            'accepted' => false,
            'external_id' => null,
            'status_code' => 0,
            'message' => 'Storecove provider not yet implemented',
            'response' => null,
        ];
    }

    public function getTransmissionStatus(string $externalId): array
    {
        // TODO: Implement Storecove status checking
        return [
            'status' => 'error',
            'ack_payload' => ['error' => 'Storecove provider not yet implemented'],
        ];
    }

    public function cancelDocument(string $externalId): array
    {
        // TODO: Implement Storecove document cancellation
        return [
            'success' => false,
            'message' => 'Storecove provider not yet implemented',
        ];
    }
}
