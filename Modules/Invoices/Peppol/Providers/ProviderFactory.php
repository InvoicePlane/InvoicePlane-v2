<?php

namespace Modules\Invoices\Peppol\Providers;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;
use ReflectionClass;

/**
 * Factory to dynamically create provider instances based on provider name.
 *
 * Providers are discovered by scanning the Connectors directory
 */
class ProviderFactory
{
    protected static ?array $providers = null;

    /**
     * Create a provider instance for the given Peppol integration.
     *
     * The provider implementation is selected using the integration's `provider_name`
     * and instantiated with the integration provided.
     *
     * @param PeppolIntegration $integration the integration containing the provider name and configuration
     *
     * @return ProviderInterface the instantiated provider configured for the given integration
     */
    public static function make(PeppolIntegration $integration): ProviderInterface
    {
        return self::makeFromName($integration->provider_name, $integration);
    }

    /**
     * Instantiate a Peppol provider by provider key.
     *
     * @param string                 $providerName the provider key (snake_case directory name) identifying which provider to create
     * @param PeppolIntegration|null $integration  optional integration model to pass to the provider constructor
     *
     * @return ProviderInterface the created provider instance
     *
     * @throws InvalidArgumentException if no provider matches the given name
     */
    public static function makeFromName(string $providerName, ?PeppolIntegration $integration = null): ProviderInterface
    {
        $providers = self::discoverProviders();

        if ( ! isset($providers[$providerName])) {
            throw new InvalidArgumentException("Unknown Peppol provider: {$providerName}");
        }

        return app($providers[$providerName], ['integration' => $integration]);
    }

    /**
     * Map discovered provider keys to user-friendly provider names.
     *
     * Names are derived from each provider class basename by removing the "Provider"
     * suffix and converting the remainder to Title Case with spaces.
     *
     * @return array<string, string> associative array mapping provider key => friendly name
     */
    public static function getAvailableProviders(): array
    {
        $providers = self::discoverProviders();
        $result    = [];

        foreach ($providers as $key => $class) {
            // Get friendly name from class name
            $className    = class_basename($class);
            $friendlyName = str_replace('Provider', '', $className);
            $friendlyName = Str::title(Str::snake($friendlyName, ' '));

            $result[$key] = $friendlyName;
        }

        return $result;
    }

    /**
     * Determines whether a provider with the given key is available.
     *
     * @param string $providerName the provider key (snake_case name derived from the provider directory)
     *
     * @return bool `true` if the provider is available, `false` otherwise
     */
    public static function isSupported(string $providerName): bool
    {
        return array_key_exists($providerName, self::discoverProviders());
    }

    /**
     * Reset the internal provider discovery cache.
     *
     * Clears the cached mapping of provider keys to class names so providers will be rediscovered on next access.
     */
    public static function clearCache(): void
    {
        self::$providers = null;
    }

    /**
     * Discovers available provider classes in the Providers directory and caches the result.
     *
     * Scans subdirectories under this class's directory for concrete classes that implement ProviderInterface
     * and registers each provider using the provider directory name converted to snake_case as the key.
     *
     * @return array<string,string> mapping of provider key to fully-qualified provider class name
     */
    protected static function discoverProviders(): array
    {
        if (self::$providers !== null) {
            return self::$providers;
        }

        self::$providers = [];

        $basePath      = __DIR__;
        $baseNamespace = 'Modules\\Invoices\\Peppol\\Providers\\';

        // Get all subdirectories (each provider has its own directory)
        $directories = glob($basePath . '/*', GLOB_ONLYDIR) ?: [];

        foreach ($directories as $directory) {
            $providerDir = basename($directory);

            // Look for a Provider class in this directory
            $providerFiles = glob($directory . '/*Provider.php') ?: [];

            foreach ($providerFiles as $file) {
                $className     = basename($file, '.php');
                $fullClassName = $baseNamespace . $providerDir . '\\' . $className;

                // Check if class exists and implements ProviderInterface
                if (class_exists($fullClassName)) {
                    $reflection = new ReflectionClass($fullClassName);
                    if ($reflection->implementsInterface(ProviderInterface::class) && ! $reflection->isAbstract()) {
                        // Convert directory name to snake_case key
                        $key                   = Str::snake($providerDir);
                        self::$providers[$key] = $fullClassName;
                    }
                }
            }
        }

        return self::$providers;
    }
}
