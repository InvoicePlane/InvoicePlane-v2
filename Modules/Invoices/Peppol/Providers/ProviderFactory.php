<?php

namespace Modules\Invoices\Peppol\Providers;

use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;
use Modules\Invoices\Peppol\Providers\EInvoiceBe\EInvoiceBeProvider;
use Modules\Invoices\Peppol\Providers\Storecove\StorecoveProvider;

/**
 * Factory to create provider instances based on provider name
 */
class ProviderFactory
{
    /**
     * Create a provider instance from integration
     */
    public static function make(PeppolIntegration $integration): ProviderInterface
    {
        return self::makeFromName($integration->provider_name, $integration);
    }

    /**
     * Create a provider instance from provider name
     */
    public static function makeFromName(string $providerName, ?PeppolIntegration $integration = null): ProviderInterface
    {
        return match ($providerName) {
            'e_invoice_be' => app(EInvoiceBeProvider::class, ['integration' => $integration]),
            'storecove' => app(StorecoveProvider::class, ['integration' => $integration]),
            default => throw new \InvalidArgumentException("Unknown Peppol provider: {$providerName}"),
        };
    }

    /**
     * Get list of available providers
     */
    public static function getAvailableProviders(): array
    {
        return [
            'e_invoice_be' => 'e-invoice.be',
            'storecove' => 'Storecove',
        ];
    }

    /**
     * Check if a provider is supported
     */
    public static function isSupported(string $providerName): bool
    {
        return array_key_exists($providerName, self::getAvailableProviders());
    }
}
