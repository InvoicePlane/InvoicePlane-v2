<?php

namespace Modules\Invoices\Peppol\Providers;

use Modules\Invoices\Enums\PeppolErrorType;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;
use Modules\Invoices\Traits\LogsPeppolActivity;

/**
 * Base abstract provider implementation with common functionality
 */
abstract class BaseProvider implements ProviderInterface
{
    use LogsPeppolActivity;

    protected ?PeppolIntegration $integration;
    protected array $config;

    public function __construct(?PeppolIntegration $integration = null)
    {
        $this->integration = $integration;
        $this->config = $integration?->config ?? [];
    }

    /**
     * Get API credentials
     */
    protected function getApiToken(): ?string
    {
        return $this->integration?->api_token ?? config("invoices.peppol.{$this->getProviderName()}.api_key");
    }

    /**
     * Get base URL
     */
    protected function getBaseUrl(): string
    {
        return $this->config['base_url'] 
            ?? config("invoices.peppol.{$this->getProviderName()}.base_url")
            ?? $this->getDefaultBaseUrl();
    }

    /**
     * Get default base URL (override in concrete providers)
     */
    abstract protected function getDefaultBaseUrl(): string;

    /**
     * Default implementation for webhook registration
     * Override in concrete providers that support webhooks
     */
    public function registerWebhookCallback(string $url, string $secret): array
    {
        return [
            'success' => false,
            'message' => 'Webhooks not supported by this provider',
        ];
    }

    /**
     * Default implementation for fetching acknowledgements
     * Override in concrete providers that support polling
     */
    public function fetchAcknowledgements(?\Carbon\Carbon $since = null): array
    {
        return [];
    }

    /**
     * Default error classification based on HTTP status codes
     * Override for provider-specific error handling
     */
    public function classifyError(int $statusCode, ?array $responseBody = null): string
    {
        return match(true) {
            $statusCode >= 500 => PeppolErrorType::TRANSIENT->value, // Server errors
            $statusCode === 429 => PeppolErrorType::TRANSIENT->value, // Rate limit
            $statusCode === 408 => PeppolErrorType::TRANSIENT->value, // Timeout
            $statusCode === 401 || $statusCode === 403 => PeppolErrorType::PERMANENT->value, // Auth errors
            $statusCode === 404 => PeppolErrorType::PERMANENT->value, // Not found
            $statusCode === 400 || $statusCode === 422 => PeppolErrorType::PERMANENT->value, // Validation errors
            default => PeppolErrorType::UNKNOWN->value,
        };
    }
}
