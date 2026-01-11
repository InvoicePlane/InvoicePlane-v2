<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Providers;

use InvalidArgumentException;
use Modules\Core\Tests\TestCase;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;
use Modules\Invoices\Peppol\Providers\EInvoiceBe\EInvoiceBeProvider;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Peppol\Providers\Storecove\StorecoveProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * ProviderFactoryTest - Unit tests for ProviderFactory.
 *
 * Tests the factory pattern for creating Peppol provider instances,
 * including provider discovery and instantiation.
 */
#[Group('peppol')]
class ProviderFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        ProviderFactory::clearCache();
    }

    protected function tearDown(): void
    {
        ProviderFactory::clearCache();
        parent::tearDown();
    }

    #[Test]
    public function it_discovers_available_providers(): void
    {
        $providers = ProviderFactory::getAvailableProviders();

        $this->assertIsArray($providers);
        $this->assertNotEmpty($providers);

        // Should have at least the two included providers
        $this->assertArrayHasKey('e_invoice_be', $providers);
        $this->assertArrayHasKey('storecove', $providers);
    }

    #[Test]
    public function it_provides_friendly_provider_names(): void
    {
        $providers = ProviderFactory::getAvailableProviders();

        // Names should be human-readable
        $this->assertEquals('E Invoice Be', $providers['e_invoice_be']);
        $this->assertEquals('Storecove', $providers['storecove']);
    }

    #[Test]
    public function it_checks_if_provider_is_supported(): void
    {
        $this->assertTrue(ProviderFactory::isSupported('e_invoice_be'));
        $this->assertTrue(ProviderFactory::isSupported('storecove'));
        $this->assertFalse(ProviderFactory::isSupported('non_existent_provider'));
    }

    #[Test]
    public function it_creates_provider_from_name_with_integration(): void
    {
        $integration = new PeppolIntegration([
            'provider_name' => 'e_invoice_be',
            'company_id'    => 1,
        ]);

        $provider = ProviderFactory::make($integration);

        $this->assertInstanceOf(ProviderInterface::class, $provider);
        $this->assertInstanceOf(EInvoiceBeProvider::class, $provider);
    }

    #[Test]
    public function it_creates_provider_from_name_string(): void
    {
        $provider = ProviderFactory::makeFromName('e_invoice_be');

        $this->assertInstanceOf(ProviderInterface::class, $provider);
        $this->assertInstanceOf(EInvoiceBeProvider::class, $provider);
    }

    #[Test]
    public function it_creates_storecove_provider(): void
    {
        $provider = ProviderFactory::makeFromName('storecove');

        $this->assertInstanceOf(ProviderInterface::class, $provider);
        $this->assertInstanceOf(StorecoveProvider::class, $provider);
    }

    #[Test]
    public function it_throws_exception_for_unknown_provider(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown Peppol provider');

        ProviderFactory::makeFromName('unknown_provider');
    }

    #[Test]
    public function it_caches_discovered_providers(): void
    {
        // First call discovers providers
        $providers1 = ProviderFactory::getAvailableProviders();

        // Second call should use cache (same result)
        $providers2 = ProviderFactory::getAvailableProviders();

        $this->assertEquals($providers1, $providers2);
    }

    #[Test]
    public function it_can_clear_provider_cache(): void
    {
        // Discover providers
        $providers1 = ProviderFactory::getAvailableProviders();

        // Clear cache
        ProviderFactory::clearCache();

        // Re-discover
        $providers2 = ProviderFactory::getAvailableProviders();

        // Should get same providers but through fresh discovery
        $this->assertEquals($providers1, $providers2);
    }

    #[Test]
    public function it_only_discovers_concrete_provider_classes(): void
    {
        $providers = ProviderFactory::getAvailableProviders();

        // All discovered providers should be instantiable
        foreach (array_keys($providers) as $providerKey) {
            $this->assertTrue(ProviderFactory::isSupported($providerKey));
        }
    }

    #[Test]
    public function it_converts_directory_names_to_snake_case_keys(): void
    {
        $providers = ProviderFactory::getAvailableProviders();

        // Directory 'EInvoiceBe' becomes 'e_invoice_be'
        $this->assertArrayHasKey('e_invoice_be', $providers);

        // Directory 'Storecove' becomes 'storecove'
        $this->assertArrayHasKey('storecove', $providers);
    }

    #[Test]
    public function it_discovers_providers_implementing_interface(): void
    {
        $providers = ProviderFactory::getAvailableProviders();

        foreach (array_keys($providers) as $providerKey) {
            $provider = ProviderFactory::makeFromName($providerKey);
            $this->assertInstanceOf(ProviderInterface::class, $provider);
        }
    }

    #[Test]
    public function it_passes_integration_to_provider_constructor(): void
    {
        $integration = new PeppolIntegration([
            'provider_name' => 'e_invoice_be',
            'company_id'    => 1,
            'enabled'       => true,
        ]);

        $provider = ProviderFactory::make($integration);

        $this->assertInstanceOf(EInvoiceBeProvider::class, $provider);
    }

    #[Test]
    public function it_handles_null_integration_gracefully(): void
    {
        $provider = ProviderFactory::makeFromName('e_invoice_be', null);

        $this->assertInstanceOf(ProviderInterface::class, $provider);
    }

    #[Test]
    public function it_resolves_provider(): void
    {
        /* Arrange */
        $integration = new PeppolIntegration([
            'provider_name' => 'storecove',
            'company_id'    => 1,
            'enabled'       => true,
        ]);

        /* Act */
        $provider = ProviderFactory::make($integration);

        /* Assert */
        $this->assertInstanceOf(ProviderInterface::class, $provider);
        $this->assertInstanceOf(StorecoveProvider::class, $provider);
    }
}
