<?php

namespace Modules\Invoices\Peppol\Providers;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;

/**
 * Base abstract provider implementation with common functionality
 */
abstract class BaseProvider implements ProviderInterface
{
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
            $statusCode >= 500 => PeppolTransmission::ERROR_TRANSIENT, // Server errors
            $statusCode === 429 => PeppolTransmission::ERROR_TRANSIENT, // Rate limit
            $statusCode === 408 => PeppolTransmission::ERROR_TRANSIENT, // Timeout
            $statusCode === 401 || $statusCode === 403 => PeppolTransmission::ERROR_PERMANENT, // Auth errors
            $statusCode === 404 => PeppolTransmission::ERROR_PERMANENT, // Not found
            $statusCode === 400 || $statusCode === 422 => PeppolTransmission::ERROR_PERMANENT, // Validation errors
            default => PeppolTransmission::ERROR_UNKNOWN,
        };
    }

    /**
     * Log provider activity
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        \Log::{$level}("[Peppol:{$this->getProviderName()}] {$message}", $context);
    }
}
