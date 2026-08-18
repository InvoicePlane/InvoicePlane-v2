<?php

namespace Modules\Invoices\Peppol\Providers;

use Modules\Invoices\Enums\PeppolErrorType;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;
use Modules\Invoices\Traits\LogsPeppolActivity;

/**
 * Base abstract provider implementation with common functionality.
 */
abstract class BaseProvider implements ProviderInterface
{
    use LogsPeppolActivity;

    protected ?PeppolIntegration $integration;

    protected array $config;

    /**
     * Initialize the provider with an optional PeppolIntegration.
     *
     * If an integration is provided, it is stored and its `config` is used; otherwise the provider's
     * configuration is initialized to an empty array.
     *
     * @param PeppolIntegration|null $integration optional integration containing provider configuration
     */
    public function __construct(?PeppolIntegration $integration = null)
    {
        $this->integration = $integration;
        $this->config      = $integration?->config ?? [];
    }

    /**
     * Provide the provider's default API base URL.
     *
     * @return string the default base URL to use when no explicit configuration is available
     */
    abstract protected function getDefaultBaseUrl(): string;

    /**
     * Indicates that webhook registration is not supported by this provider.
     *
     * @param string $url    the webhook callback URL to register
     * @param string $secret the shared secret used to sign or verify callbacks
     *
     * @return array{success:bool,message:string} an associative array with `success` set to `false` and a human-readable `message`
     */
    public function registerWebhookCallback(string $url, string $secret): array
    {
        return [
            'success' => false,
            'message' => 'Webhooks not supported by this provider',
        ];
    }

    /**
     * Retrieve Peppol acknowledgements available since an optional timestamp.
     *
     * Providers that support polling should override this method to return acknowledgement records.
     *
     * @param \Carbon\Carbon|null $since an optional cutoff; only acknowledgements at or after this time should be returned
     *
     * @return array an array of acknowledgement entries; empty by default
     */
    public function fetchAcknowledgements(?\Carbon\Carbon $since = null): array
    {
        return [];
    }

    /**
     * Classifies an HTTP response into a Peppol error category.
     *
     * Defaults to mapping server errors, rate limits, and timeouts to `PeppolErrorType::TRANSIENT`;
     * authentication, client/validation and not-found errors to `PeppolErrorType::PERMANENT`;
     * and all other statuses to `PeppolErrorType::UNKNOWN`. Providers may override for custom rules.
     *
     * @param int        $statusCode   the HTTP status code to classify
     * @param array|null $responseBody optional parsed response body from the provider; available for provider-specific overrides
     *
     * @return string one of the `PeppolErrorType` values (`TRANSIENT`, `PERMANENT`, or `UNKNOWN`) as a string
     */
    public function classifyError(int $statusCode, ?array $responseBody = null): string
    {
        return match(true) {
            $statusCode >= 500                         => PeppolErrorType::TRANSIENT->value, // Server errors
            $statusCode === 429                        => PeppolErrorType::TRANSIENT->value, // Rate limit
            $statusCode === 408                        => PeppolErrorType::TRANSIENT->value, // Timeout
            $statusCode === 401 || $statusCode === 403 => PeppolErrorType::PERMANENT->value, // Auth errors
            $statusCode === 404                        => PeppolErrorType::PERMANENT->value, // Not found
            $statusCode === 400 || $statusCode === 422 => PeppolErrorType::PERMANENT->value, // Validation errors
            default                                    => PeppolErrorType::UNKNOWN->value,
        };
    }

    /**
     * Resolve the provider's base URL.
     *
     * Looks up a base URL from the provider instance config, then from the application
     * configuration for the provider, and falls back to the provider's default.
     *
     * @return string The resolved base URL. */
    protected function getBaseUrl(): string
    {
        return $this->config['base_url']
            ?? config("invoices.peppol.{$this->getProviderName()}.base_url")
            ?? $this->getDefaultBaseUrl();
    }

    /**
     * Default authenticate implementation for providers using static credentials.
     *
     * Checks that all required settings from the provider's schema are present and non-empty.
     * This default implementation is used by EInvoiceBeProvider, QontoProvider, and StorecoveProvider.
     * OAuth2 providers (LetsPeppolProvider, SuperPdpProvider) override this with their own logic.
     *
     * @return bool true if all required settings are configured, false otherwise
     */
    public function authenticate(): bool
    {
        $settings = static::settings();

        foreach ($settings as $key => $metadata) {
            if ($metadata['required'] ?? false) {
                if (empty($this->config[$key])) {
                    return false;
                }
            }
        }

        return true;
    }
}
