<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\PeppolConnectionStatus;
use Modules\Invoices\Enums\PeppolValidationStatus;
use Modules\Invoices\Events\Peppol\PeppolIdValidationCompleted;
use Modules\Invoices\Events\Peppol\PeppolIntegrationCreated;
use Modules\Invoices\Events\Peppol\PeppolIntegrationTested;
use Modules\Invoices\Models\CustomerPeppolValidationHistory;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Peppol\Services\PeppolManagementService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolManagementServiceTest - Unit tests for PeppolManagementService.
 *
 * Tests integration management, validation, and orchestration logic.
 *
 * @package Modules\Invoices\Tests\Unit\Peppol\Services
 */
class PeppolManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PeppolManagementService $service;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PeppolManagementService();
        $this->company = Company::factory()->create();
    }

    #[Test]
    public function it_creates_a_new_peppol_integration(): void
    {
        Event::fake();

        $config = [
            'api_endpoint' => 'https://api.example.com',
            'timeout' => '30',
        ];

        $integration = $this->service->createIntegration(
            $this->company->id,
            'e_invoice_be',
            $config,
            'test-api-token'
        );

        $this->assertInstanceOf(PeppolIntegration::class, $integration);
        $this->assertEquals($this->company->id, $integration->company_id);
        $this->assertEquals('e_invoice_be', $integration->provider_name);
        $this->assertFalse($integration->enabled); // Should start disabled
        $this->assertEquals('test-api-token', $integration->api_token);

        Event::assertDispatched(PeppolIntegrationCreated::class, function ($event) use ($integration) {
            return $event->integration->id === $integration->id;
        });
    }

    #[Test]
    public function it_stores_configuration_when_creating_integration(): void
    {
        $config = [
            'api_endpoint' => 'https://api.example.com',
            'timeout' => '30',
            'max_retries' => '5',
        ];

        $integration = $this->service->createIntegration(
            $this->company->id,
            'e_invoice_be',
            $config
        );

        $this->assertEquals(3, $integration->configurations()->count());
        $this->assertEquals('https://api.example.com', $integration->getConfigValue('api_endpoint'));
        $this->assertEquals('30', $integration->getConfigValue('timeout'));
        $this->assertEquals('5', $integration->getConfigValue('max_retries'));
    }

    #[Test]
    public function it_encrypts_api_token_when_creating_integration(): void
    {
        $plainToken = 'super-secret-token';

        $integration = $this->service->createIntegration(
            $this->company->id,
            'e_invoice_be',
            [],
            $plainToken
        );

        $this->assertNotEquals($plainToken, $integration->encrypted_api_token);
        $this->assertEquals($plainToken, $integration->api_token);
    }

    #[Test]
    public function it_handles_null_api_token_when_creating_integration(): void
    {
        $integration = $this->service->createIntegration(
            $this->company->id,
            'e_invoice_be',
            [],
            null
        );

        $this->assertNull($integration->encrypted_api_token);
        $this->assertNull($integration->api_token);
    }

    #[Test]
    public function it_rolls_back_transaction_on_error_during_creation(): void
    {
        $this->expectException(\Exception::class);

        // Use invalid company ID to force error
        $this->service->createIntegration(
            999999,
            'e_invoice_be',
            []
        );

        // Should not have created any records
        $this->assertEquals(0, PeppolIntegration::count());
    }

    #[Test]
    public function it_tests_connection_successfully(): void
    {
        Event::fake();

        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
            'test_connection_status' => PeppolConnectionStatus::UNTESTED,
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('testConnection')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn([
                'ok' => true,
                'message' => 'Connection successful',
            ]);

        ProviderFactory::shouldReceive('make')
            ->with($integration)
            ->once()
            ->andReturn($providerMock);

        $result = $this->service->testConnection($integration);

        $this->assertTrue($result['ok']);
        $this->assertEquals('Connection successful', $result['message']);

        $integration->refresh();
        $this->assertEquals(PeppolConnectionStatus::SUCCESS, $integration->test_connection_status);
        $this->assertNotNull($integration->test_connection_at);

        Event::assertDispatched(PeppolIntegrationTested::class);
    }

    #[Test]
    public function it_tests_connection_and_records_failure(): void
    {
        Event::fake();

        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('testConnection')
            ->once()
            ->andReturn([
                'ok' => false,
                'message' => 'Invalid API key',
            ]);

        ProviderFactory::shouldReceive('make')
            ->once()
            ->andReturn($providerMock);

        $result = $this->service->testConnection($integration);

        $this->assertFalse($result['ok']);
        $this->assertEquals('Invalid API key', $result['message']);

        $integration->refresh();
        $this->assertEquals(PeppolConnectionStatus::FAILED, $integration->test_connection_status);
        $this->assertEquals('Invalid API key', $integration->test_connection_message);

        Event::assertDispatched(PeppolIntegrationTested::class);
    }

    #[Test]
    public function it_handles_exception_during_connection_test(): void
    {
        Event::fake();

        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('testConnection')
            ->once()
            ->andThrow(new \Exception('Network error'));

        ProviderFactory::shouldReceive('make')
            ->once()
            ->andReturn($providerMock);

        $result = $this->service->testConnection($integration);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Network error', $result['message']);

        $integration->refresh();
        $this->assertEquals(PeppolConnectionStatus::FAILED, $integration->test_connection_status);
    }

    #[Test]
    public function it_validates_peppol_id_successfully(): void
    {
        Event::fake();

        $customer = Relation::factory()->create([
            'company_id' => $this->company->id,
            'peppol_id' => '0123:123456789',
            'peppol_scheme' => '0123',
        ]);

        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('validatePeppolId')
            ->once()
            ->with('0123', '123456789')
            ->andReturn([
                'present' => true,
                'details' => [
                    'participant_id' => '0123:123456789',
                    'registered' => true,
                ],
            ]);

        ProviderFactory::shouldReceive('make')
            ->once()
            ->andReturn($providerMock);

        $result = $this->service->validateCustomerPeppolId($customer, $integration);

        $this->assertEquals(PeppolValidationStatus::VALID, $result['status']);
        $this->assertTrue($result['is_valid']);

        // Check validation history was created
        $history = CustomerPeppolValidationHistory::where('customer_id', $customer->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals(PeppolValidationStatus::VALID, $history->status);

        Event::assertDispatched(PeppolIdValidationCompleted::class);
    }

    #[Test]
    public function it_validates_peppol_id_as_not_found(): void
    {
        Event::fake();

        $customer = Relation::factory()->create([
            'company_id' => $this->company->id,
            'peppol_id' => '0123:999999999',
            'peppol_scheme' => '0123',
        ]);

        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('validatePeppolId')
            ->once()
            ->andReturn([
                'present' => false,
                'details' => null,
            ]);

        ProviderFactory::shouldReceive('make')
            ->once()
            ->andReturn($providerMock);

        $result = $this->service->validateCustomerPeppolId($customer, $integration);

        $this->assertEquals(PeppolValidationStatus::NOT_FOUND, $result['status']);
        $this->assertFalse($result['is_valid']);
    }

    #[Test]
    public function it_handles_validation_errors(): void
    {
        $customer = Relation::factory()->create([
            'company_id' => $this->company->id,
            'peppol_id' => '0123:123456789',
            'peppol_scheme' => '0123',
        ]);

        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('validatePeppolId')
            ->once()
            ->andReturn([
                'present' => false,
                'details' => ['error' => 'API rate limit exceeded'],
            ]);

        ProviderFactory::shouldReceive('make')
            ->once()
            ->andReturn($providerMock);

        $result = $this->service->validateCustomerPeppolId($customer, $integration);

        $this->assertEquals(PeppolValidationStatus::ERROR, $result['status']);
        $this->assertFalse($result['is_valid']);
    }

    #[Test]
    public function it_requires_peppol_id_and_scheme_for_validation(): void
    {
        $customer = Relation::factory()->create([
            'company_id' => $this->company->id,
            'peppol_id' => null,
            'peppol_scheme' => null,
        ]);

        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $result = $this->service->validateCustomerPeppolId($customer, $integration);

        $this->assertEquals(PeppolValidationStatus::INVALID, $result['status']);
        $this->assertFalse($result['is_valid']);
        $this->assertStringContainsString('Peppol ID', $result['message']);
    }

    #[Test]
    public function it_enables_integration(): void
    {
        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
            'enabled' => false,
            'test_connection_status' => PeppolConnectionStatus::SUCCESS,
        ]);

        $this->service->enableIntegration($integration);

        $integration->refresh();
        $this->assertTrue($integration->enabled);
    }

    #[Test]
    public function it_disables_integration(): void
    {
        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
            'enabled' => true,
        ]);

        $this->service->disableIntegration($integration);

        $integration->refresh();
        $this->assertFalse($integration->enabled);
    }

    #[Test]
    public function it_sends_invoice_to_peppol(): void
    {
        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
            'enabled' => true,
            'test_connection_status' => PeppolConnectionStatus::SUCCESS,
        ]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        // Mock job dispatch
        \Illuminate\Support\Facades\Bus::fake();

        $result = $this->service->sendInvoiceToPeppol($invoice, $integration);

        $this->assertTrue($result['dispatched']);

        \Illuminate\Support\Facades\Bus::assertDispatched(\Modules\Invoices\Jobs\Peppol\SendInvoiceToPeppolJob::class);
    }

    #[Test]
    public function it_prevents_sending_with_disabled_integration(): void
    {
        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
            'enabled' => false, // Disabled
        ]);

        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $result = $this->service->sendInvoiceToPeppol($invoice, $integration);

        $this->assertFalse($result['dispatched']);
        $this->assertStringContainsString('not enabled', $result['error']);
    }

    #[Test]
    public function it_gets_integration_status(): void
    {
        $integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
            'enabled' => true,
            'test_connection_status' => PeppolConnectionStatus::SUCCESS,
        ]);

        $status = $this->service->getIntegrationStatus($integration);

        $this->assertTrue($status['enabled']);
        $this->assertTrue($status['connection_ok']);
        $this->assertTrue($status['ready']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}