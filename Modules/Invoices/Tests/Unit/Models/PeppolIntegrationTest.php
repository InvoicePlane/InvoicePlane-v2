<?php

namespace Modules\Invoices\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\PeppolConnectionStatus;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolIntegrationConfig;
use Modules\Invoices\Models\PeppolTransmission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolIntegrationTest - Unit tests for PeppolIntegration model.
 *
 * Tests model relationships, encryption, configuration management, and status checks.
 *
 * @package Modules\Invoices\Tests\Unit\Models
 */
class PeppolIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected PeppolIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->integration = new PeppolIntegration([
            'company_id' => $this->company->id,
            'provider_name' => 'e_invoice_be',
            'enabled' => false,
            'test_connection_status' => PeppolConnectionStatus::UNTESTED,
        ]);
        $this->integration->save();
    }

    #[Test]
    public function it_can_be_created_with_required_fields(): void
    {
        $integration = PeppolIntegration::create([
            'company_id' => $this->company->id,
            'provider_name' => 'e_invoice_be',
            'enabled' => false,
            'test_connection_status' => PeppolConnectionStatus::UNTESTED,
        ]);

        $this->assertInstanceOf(PeppolIntegration::class, $integration);
        $this->assertEquals($this->company->id, $integration->company_id);
        $this->assertEquals('e_invoice_be', $integration->provider_name);
        $this->assertFalse($integration->enabled);
        $this->assertEquals(PeppolConnectionStatus::UNTESTED, $integration->test_connection_status);
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        $this->assertInstanceOf(Company::class, $this->integration->company);
        $this->assertEquals($this->company->id, $this->integration->company->id);
    }

    #[Test]
    public function it_has_many_transmissions(): void
    {
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
        ]);

        $this->assertTrue($this->integration->transmissions()->exists());
        $this->assertEquals($transmission->id, $this->integration->transmissions->first()->id);
    }

    #[Test]
    public function it_has_many_configurations(): void
    {
        $config = PeppolIntegrationConfig::create([
            'integration_id' => $this->integration->id,
            'config_key' => 'api_endpoint',
            'config_value' => 'https://api.example.com',
        ]);

        $this->assertTrue($this->integration->configurations()->exists());
        $this->assertEquals($config->id, $this->integration->configurations->first()->id);
    }

    #[Test]
    public function it_encrypts_api_token_when_set(): void
    {
        $plainToken = 'super-secret-token-123';
        
        $this->integration->api_token = $plainToken;
        $this->integration->save();

        // The encrypted value should be different from the plain token
        $this->assertNotEquals($plainToken, $this->integration->encrypted_api_token);
        $this->assertNotNull($this->integration->encrypted_api_token);
    }

    #[Test]
    public function it_decrypts_api_token_when_accessed(): void
    {
        $plainToken = 'super-secret-token-123';
        
        $this->integration->api_token = $plainToken;
        $this->integration->save();

        // Fresh retrieval should decrypt correctly
        $retrieved = PeppolIntegration::find($this->integration->id);
        $this->assertEquals($plainToken, $retrieved->api_token);
    }

    #[Test]
    public function it_handles_null_api_token(): void
    {
        $this->integration->api_token = null;
        $this->integration->save();

        $this->assertNull($this->integration->encrypted_api_token);
        $this->assertNull($this->integration->api_token);
    }

    #[Test]
    public function it_can_set_configuration_as_array(): void
    {
        $config = [
            'api_endpoint' => 'https://api.example.com',
            'timeout' => '30',
            'max_retries' => '5',
        ];

        $this->integration->setConfig($config);

        $this->assertEquals(3, $this->integration->configurations()->count());
        $this->assertEquals('https://api.example.com', $this->integration->getConfigValue('api_endpoint'));
        $this->assertEquals('30', $this->integration->getConfigValue('timeout'));
        $this->assertEquals('5', $this->integration->getConfigValue('max_retries'));
    }

    #[Test]
    public function it_can_get_configuration_as_array(): void
    {
        $config = [
            'api_endpoint' => 'https://api.example.com',
            'timeout' => '30',
        ];

        $this->integration->setConfig($config);
        $this->integration->refresh();

        $retrievedConfig = $this->integration->config;
        
        $this->assertIsArray($retrievedConfig);
        $this->assertEquals('https://api.example.com', $retrievedConfig['api_endpoint']);
        $this->assertEquals('30', $retrievedConfig['timeout']);
    }

    #[Test]
    public function it_updates_existing_configuration_values(): void
    {
        $this->integration->setConfig(['api_endpoint' => 'https://old-api.example.com']);
        $this->integration->setConfig(['api_endpoint' => 'https://new-api.example.com']);

        $this->assertEquals(1, $this->integration->configurations()->count());
        $this->assertEquals('https://new-api.example.com', $this->integration->getConfigValue('api_endpoint'));
    }

    #[Test]
    public function it_can_get_individual_config_value(): void
    {
        $this->integration->setConfig(['api_endpoint' => 'https://api.example.com']);

        $value = $this->integration->getConfigValue('api_endpoint');
        
        $this->assertEquals('https://api.example.com', $value);
    }

    #[Test]
    public function it_returns_default_for_missing_config_value(): void
    {
        $value = $this->integration->getConfigValue('nonexistent_key', 'default_value');
        
        $this->assertEquals('default_value', $value);
    }

    #[Test]
    public function it_returns_null_for_missing_config_value_without_default(): void
    {
        $value = $this->integration->getConfigValue('nonexistent_key');
        
        $this->assertNull($value);
    }

    #[Test]
    public function is_connection_successful_returns_true_when_status_is_success(): void
    {
        $this->integration->test_connection_status = PeppolConnectionStatus::SUCCESS;
        $this->integration->save();

        $this->assertTrue($this->integration->isConnectionSuccessful());
    }

    #[Test]
    public function is_connection_successful_returns_false_when_status_is_failed(): void
    {
        $this->integration->test_connection_status = PeppolConnectionStatus::FAILED;
        $this->integration->save();

        $this->assertFalse($this->integration->isConnectionSuccessful());
    }

    #[Test]
    public function is_connection_successful_returns_false_when_status_is_untested(): void
    {
        $this->integration->test_connection_status = PeppolConnectionStatus::UNTESTED;
        $this->integration->save();

        $this->assertFalse($this->integration->isConnectionSuccessful());
    }

    #[Test]
    public function is_ready_returns_true_when_enabled_and_connection_successful(): void
    {
        $this->integration->enabled = true;
        $this->integration->test_connection_status = PeppolConnectionStatus::SUCCESS;
        $this->integration->save();

        $this->assertTrue($this->integration->isReady());
    }

    #[Test]
    public function is_ready_returns_false_when_disabled(): void
    {
        $this->integration->enabled = false;
        $this->integration->test_connection_status = PeppolConnectionStatus::SUCCESS;
        $this->integration->save();

        $this->assertFalse($this->integration->isReady());
    }

    #[Test]
    public function is_ready_returns_false_when_connection_failed(): void
    {
        $this->integration->enabled = true;
        $this->integration->test_connection_status = PeppolConnectionStatus::FAILED;
        $this->integration->save();

        $this->assertFalse($this->integration->isReady());
    }

    #[Test]
    public function is_ready_returns_false_when_both_disabled_and_connection_failed(): void
    {
        $this->integration->enabled = false;
        $this->integration->test_connection_status = PeppolConnectionStatus::FAILED;
        $this->integration->save();

        $this->assertFalse($this->integration->isReady());
    }

    #[Test]
    public function it_casts_test_connection_status_to_enum(): void
    {
        $this->integration->test_connection_status = PeppolConnectionStatus::SUCCESS;
        $this->integration->save();

        $retrieved = PeppolIntegration::find($this->integration->id);
        
        $this->assertInstanceOf(PeppolConnectionStatus::class, $retrieved->test_connection_status);
        $this->assertEquals(PeppolConnectionStatus::SUCCESS, $retrieved->test_connection_status);
    }

    #[Test]
    public function it_casts_enabled_to_boolean(): void
    {
        $this->integration->enabled = 1;
        $this->integration->save();

        $retrieved = PeppolIntegration::find($this->integration->id);
        
        $this->assertIsBool($retrieved->enabled);
        $this->assertTrue($retrieved->enabled);
    }

    #[Test]
    public function it_casts_test_connection_at_to_datetime(): void
    {
        $now = now();
        $this->integration->test_connection_at = $now;
        $this->integration->save();

        $retrieved = PeppolIntegration::find($this->integration->id);
        
        $this->assertInstanceOf(\Carbon\Carbon::class, $retrieved->test_connection_at);
        $this->assertEquals($now->toDateTimeString(), $retrieved->test_connection_at->toDateTimeString());
    }

    #[Test]
    public function it_can_store_multiple_configuration_entries(): void
    {
        $config = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->integration->setConfig($config);

        $this->assertEquals(3, $this->integration->configurations()->count());
    }

    #[Test]
    public function configuration_values_can_be_complex_strings(): void
    {
        $config = [
            'json_data' => json_encode(['nested' => 'value']),
            'url' => 'https://api.example.com/v1/endpoint?key=value&other=123',
        ];

        $this->integration->setConfig($config);

        $this->assertEquals(json_encode(['nested' => 'value']), $this->integration->getConfigValue('json_data'));
        $this->assertEquals('https://api.example.com/v1/endpoint?key=value&other=123', $this->integration->getConfigValue('url'));
    }
}