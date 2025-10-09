<?php

namespace Modules\Invoices\Peppol\Providers;

use Illuminate\Support\Str;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;

/**
 * Factory to dynamically create provider instances based on provider name
 * 
 * Providers are discovered by scanning the Connectors directory
 */
class ProviderFactory
{
    protected static ?array $providers = null;

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
        $providers = self::discoverProviders();

        if (!isset($providers[$providerName])) {
            throw new \InvalidArgumentException("Unknown Peppol provider: {$providerName}");
        }

        return app($providers[$providerName], ['integration' => $integration]);
    }

    /**
     * Get list of available providers by scanning Connectors directory
     */
    public static function getAvailableProviders(): array
    {
        $providers = self::discoverProviders();
        $result = [];

        foreach ($providers as $key => $class) {
            // Get friendly name from class name
            $className = class_basename($class);
            $friendlyName = str_replace('Provider', '', $className);
            $friendlyName = Str::title(Str::snake($friendlyName, ' '));
            
            $result[$key] = $friendlyName;
        }

        return $result;
    }

    /**
     * Check if a provider is supported
     */
    public static function isSupported(string $providerName): bool
    {
        return array_key_exists($providerName, self::discoverProviders());
    }

    /**
     * Discover all provider classes by scanning directories
     */
    protected static function discoverProviders(): array
    {
        if (self::$providers !== null) {
            return self::$providers;
        }

        self::$providers = [];

        $basePath = __DIR__;
        $baseNamespace = 'Modules\\Invoices\\Peppol\\Providers\\';

        // Get all subdirectories (each provider has its own directory)
        $directories = glob($basePath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            $providerDir = basename($directory);
            
            // Look for a Provider class in this directory
            $providerFiles = glob($directory . '/*Provider.php');
            
            foreach ($providerFiles as $file) {
                $className = basename($file, '.php');
                $fullClassName = $baseNamespace . $providerDir . '\\' . $className;

                // Check if class exists and implements ProviderInterface
                if (class_exists($fullClassName)) {
                    $reflection = new \ReflectionClass($fullClassName);
                    if ($reflection->implementsInterface(ProviderInterface::class) && !$reflection->isAbstract()) {
                        // Convert directory name to snake_case key
                        $key = Str::snake($providerDir);
                        self::$providers[$key] = $fullClassName;
                    }
                }
            }
        }

        return self::$providers;
    }

    /**
     * Clear the discovered providers cache
     */
    public static function clearCache(): void
    {
        self::$providers = null;
    }
}
