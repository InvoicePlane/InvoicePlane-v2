<?php

namespace Modules\Invoices\Tests\Unit\Jobs\Peppol;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\PeppolTransmissionStatus;
use Modules\Invoices\Events\Peppol\PeppolAcknowledgementReceived;
use Modules\Invoices\Jobs\Peppol\PeppolStatusPoller;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Peppol\Contracts\ProviderInterface;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolStatusPollerTest - Unit tests for PeppolStatusPoller job.
 *
 * Tests polling logic, status updates, and event dispatching.
 *
 * @package Modules\Invoices\Tests\Unit\Jobs\Peppol
 */
class PeppolStatusPollerTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected PeppolIntegration $integration;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $this->invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    #[Test]
    public function it_polls_transmissions_awaiting_acknowledgement(): void
    {
        Event::fake();

        // Create a transmission that should be polled
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => 'EXT-12345',
            'sent_at' => now()->subMinutes(10),
            'acknowledged_at' => null,
        ]);

        // Mock provider
        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('getTransmissionStatus')
            ->with('EXT-12345')
            ->once()
            ->andReturn([
                'status' => 'delivered',
                'ack_payload' => ['message' => 'Successfully delivered'],
            ]);

        ProviderFactory::shouldReceive('make')
            ->with($this->integration)
            ->once()
            ->andReturn($providerMock);

        // Execute the job
        $job = new PeppolStatusPoller();
        $job->handle();

        // Assert transmission was marked as accepted
        $transmission->refresh();
        $this->assertEquals(PeppolTransmissionStatus::ACCEPTED, $transmission->status);
        $this->assertNotNull($transmission->acknowledged_at);

        Event::assertDispatched(PeppolAcknowledgementReceived::class);
    }

    #[Test]
    public function it_skips_transmissions_sent_recently(): void
    {
        // Create a transmission sent less than 5 minutes ago
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => 'EXT-12345',
            'sent_at' => now()->subMinutes(3), // Less than grace period
            'acknowledged_at' => null,
        ]);

        ProviderFactory::shouldReceive('make')->never();

        $job = new PeppolStatusPoller();
        $job->handle();

        // Status should remain unchanged
        $transmission->refresh();
        $this->assertEquals(PeppolTransmissionStatus::SENT, $transmission->status);
    }

    #[Test]
    public function it_marks_transmission_as_rejected_when_status_is_rejected(): void
    {
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => 'EXT-12345',
            'sent_at' => now()->subMinutes(10),
            'acknowledged_at' => null,
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('getTransmissionStatus')
            ->with('EXT-12345')
            ->once()
            ->andReturn([
                'status' => 'rejected',
                'ack_payload' => ['message' => 'Invalid VAT number'],
            ]);

        ProviderFactory::shouldReceive('make')
            ->with($this->integration)
            ->once()
            ->andReturn($providerMock);

        $job = new PeppolStatusPoller();
        $job->handle();

        $transmission->refresh();
        $this->assertEquals(PeppolTransmissionStatus::REJECTED, $transmission->status);
    }

    #[Test]
    public function it_handles_multiple_transmissions_in_batch(): void
    {
        Event::fake();

        // Create multiple transmissions
        $transmissions = [];
        for ($i = 0; $i < 5; $i++) {
            $transmissions[] = PeppolTransmission::factory()->create([
                'integration_id' => $this->integration->id,
                'invoice_id' => $this->invoice->id,
                'status' => PeppolTransmissionStatus::SENT,
                'external_id' => "EXT-{$i}",
                'sent_at' => now()->subMinutes(10),
                'acknowledged_at' => null,
            ]);
        }

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('getTransmissionStatus')
            ->times(5)
            ->andReturn(['status' => 'delivered']);

        ProviderFactory::shouldReceive('make')
            ->times(5)
            ->andReturn($providerMock);

        $job = new PeppolStatusPoller();
        $job->handle();

        foreach ($transmissions as $transmission) {
            $transmission->refresh();
            $this->assertEquals(PeppolTransmissionStatus::ACCEPTED, $transmission->status);
        }
    }

    #[Test]
    public function it_continues_processing_after_individual_failure(): void
    {
        $transmission1 = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => 'EXT-1',
            'sent_at' => now()->subMinutes(10),
        ]);

        $transmission2 = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => 'EXT-2',
            'sent_at' => now()->subMinutes(10),
        ]);

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('getTransmissionStatus')
            ->with('EXT-1')
            ->once()
            ->andThrow(new \Exception('API error'));

        $providerMock->shouldReceive('getTransmissionStatus')
            ->with('EXT-2')
            ->once()
            ->andReturn(['status' => 'delivered']);

        ProviderFactory::shouldReceive('make')
            ->twice()
            ->andReturn($providerMock);

        $job = new PeppolStatusPoller();
        $job->handle();

        // First transmission should still be SENT due to error
        $transmission1->refresh();
        $this->assertEquals(PeppolTransmissionStatus::SENT, $transmission1->status);

        // Second transmission should be ACCEPTED
        $transmission2->refresh();
        $this->assertEquals(PeppolTransmissionStatus::ACCEPTED, $transmission2->status);
    }

    #[Test]
    public function it_ignores_transmissions_without_external_id(): void
    {
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => null, // No external ID
            'sent_at' => now()->subMinutes(10),
        ]);

        ProviderFactory::shouldReceive('make')->never();

        $job = new PeppolStatusPoller();
        $job->handle();

        $transmission->refresh();
        $this->assertEquals(PeppolTransmissionStatus::SENT, $transmission->status);
    }

    #[Test]
    public function it_ignores_already_acknowledged_transmissions(): void
    {
        PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => 'EXT-12345',
            'sent_at' => now()->subMinutes(10),
            'acknowledged_at' => now()->subMinutes(5), // Already acknowledged
        ]);

        ProviderFactory::shouldReceive('make')->never();

        $job = new PeppolStatusPoller();
        $job->handle();
    }

    #[Test]
    public function it_processes_maximum_of_100_transmissions_per_batch(): void
    {
        // Create more than 100 transmissions
        for ($i = 0; $i < 150; $i++) {
            PeppolTransmission::factory()->create([
                'integration_id' => $this->integration->id,
                'invoice_id' => $this->invoice->id,
                'status' => PeppolTransmissionStatus::SENT,
                'external_id' => "EXT-{$i}",
                'sent_at' => now()->subMinutes(10),
                'acknowledged_at' => null,
            ]);
        }

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('getTransmissionStatus')
            ->times(100) // Should only process 100
            ->andReturn(['status' => 'delivered']);

        ProviderFactory::shouldReceive('make')
            ->times(100)
            ->andReturn($providerMock);

        $job = new PeppolStatusPoller();
        $job->handle();

        // Verify only 100 were processed
        $accepted = PeppolTransmission::where('status', PeppolTransmissionStatus::ACCEPTED)->count();
        $this->assertEquals(100, $accepted);
    }

    #[Test]
    public function it_updates_provider_response_when_available(): void
    {
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => 'EXT-12345',
            'sent_at' => now()->subMinutes(10),
        ]);

        $ackPayload = [
            'document_id' => 'DOC-789',
            'recipient_confirmed' => true,
            'timestamp' => '2025-01-15T10:00:00Z',
        ];

        $providerMock = Mockery::mock(ProviderInterface::class);
        $providerMock->shouldReceive('getTransmissionStatus')
            ->once()
            ->andReturn([
                'status' => 'delivered',
                'ack_payload' => $ackPayload,
            ]);

        ProviderFactory::shouldReceive('make')
            ->once()
            ->andReturn($providerMock);

        $job = new PeppolStatusPoller();
        $job->handle();

        $transmission->refresh();
        $response = $transmission->provider_response;
        
        $this->assertArrayHasKey('document_id', $response);
        $this->assertEquals('DOC-789', $response['document_id']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}