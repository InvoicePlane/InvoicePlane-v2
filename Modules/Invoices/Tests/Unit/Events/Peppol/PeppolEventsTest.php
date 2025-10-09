<?php

namespace Modules\Invoices\Tests\Unit\Events\Peppol;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Invoices\Events\Peppol\PeppolAcknowledgementReceived;
use Modules\Invoices\Events\Peppol\PeppolIdValidationCompleted;
use Modules\Invoices\Events\Peppol\PeppolIntegrationCreated;
use Modules\Invoices\Events\Peppol\PeppolIntegrationTested;
use Modules\Invoices\Events\Peppol\PeppolTransmissionCreated;
use Modules\Invoices\Events\Peppol\PeppolTransmissionDead;
use Modules\Invoices\Events\Peppol\PeppolTransmissionFailed;
use Modules\Invoices\Events\Peppol\PeppolTransmissionPrepared;
use Modules\Invoices\Events\Peppol\PeppolTransmissionSent;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolEventsTest - Unit tests for Peppol event classes.
 *
 * Tests event instantiation, payload structure, and event names.
 *
 * @package Modules\Invoices\Tests\Unit\Events\Peppol
 */
class PeppolEventsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected PeppolIntegration $integration;
    protected PeppolTransmission $transmission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
        $this->transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $invoice->id,
        ]);
    }

    #[Test]
    public function peppol_integration_created_event_has_correct_structure(): void
    {
        $event = new PeppolIntegrationCreated($this->integration);

        $this->assertInstanceOf(PeppolIntegrationCreated::class, $event);
        $this->assertEquals($this->integration->id, $event->integration->id);
        $this->assertEquals('PeppolIntegrationCreated', $event->getEventName());

        $payload = $event->getAuditPayload();
        $this->assertArrayHasKey('integration_id', $payload);
        $this->assertEquals($this->integration->id, $payload['integration_id']);
    }

    #[Test]
    public function peppol_integration_tested_event_has_correct_structure(): void
    {
        $event = new PeppolIntegrationTested($this->integration, true, 'Success');

        $this->assertEquals($this->integration->id, $event->integration->id);
        $this->assertTrue($event->success);
        $this->assertEquals('Success', $event->message);
        $this->assertEquals('PeppolIntegrationTested', $event->getEventName());

        $payload = $event->getAuditPayload();
        $this->assertArrayHasKey('integration_id', $payload);
        $this->assertArrayHasKey('success', $payload);
        $this->assertArrayHasKey('message', $payload);
    }

    #[Test]
    public function peppol_id_validation_completed_event_has_correct_structure(): void
    {
        $customer = Relation::factory()->create(['company_id' => $this->company->id]);
        $event = new PeppolIdValidationCompleted(
            $customer,
            'valid',
            'Participant found'
        );

        $this->assertEquals($customer->id, $event->customer->id);
        $this->assertEquals('valid', $event->status);
        $this->assertEquals('Participant found', $event->message);
        $this->assertEquals('PeppolIdValidationCompleted', $event->getEventName());
    }

    #[Test]
    public function peppol_transmission_created_event_has_correct_structure(): void
    {
        $event = new PeppolTransmissionCreated($this->transmission);

        $this->assertEquals($this->transmission->id, $event->transmission->id);
        $this->assertEquals('PeppolTransmissionCreated', $event->getEventName());

        $payload = $event->getAuditPayload();
        $this->assertArrayHasKey('transmission_id', $payload);
    }

    #[Test]
    public function peppol_transmission_prepared_event_has_correct_structure(): void
    {
        $event = new PeppolTransmissionPrepared($this->transmission);

        $this->assertEquals($this->transmission->id, $event->transmission->id);
        $this->assertEquals('PeppolTransmissionPrepared', $event->getEventName());
    }

    #[Test]
    public function peppol_transmission_sent_event_has_correct_structure(): void
    {
        $event = new PeppolTransmissionSent($this->transmission, 'EXT-12345');

        $this->assertEquals($this->transmission->id, $event->transmission->id);
        $this->assertEquals('EXT-12345', $event->externalId);
        $this->assertEquals('PeppolTransmissionSent', $event->getEventName());

        $payload = $event->getAuditPayload();
        $this->assertArrayHasKey('external_id', $payload);
    }

    #[Test]
    public function peppol_acknowledgement_received_event_has_correct_structure(): void
    {
        $ackPayload = ['status' => 'delivered', 'timestamp' => '2025-01-15T10:00:00Z'];
        $event = new PeppolAcknowledgementReceived($this->transmission, $ackPayload);

        $this->assertEquals($this->transmission->id, $event->transmission->id);
        $this->assertEquals($ackPayload, $event->ackPayload);
        $this->assertEquals('PeppolAcknowledgementReceived', $event->getEventName());
    }

    #[Test]
    public function peppol_transmission_failed_event_has_correct_structure(): void
    {
        $event = new PeppolTransmissionFailed(
            $this->transmission,
            'Connection timeout',
            'network'
        );

        $this->assertEquals($this->transmission->id, $event->transmission->id);
        $this->assertEquals('Connection timeout', $event->error);
        $this->assertEquals('network', $event->errorType);
        $this->assertEquals('PeppolTransmissionFailed', $event->getEventName());

        $payload = $event->getAuditPayload();
        $this->assertArrayHasKey('error', $payload);
        $this->assertArrayHasKey('error_type', $payload);
    }

    #[Test]
    public function peppol_transmission_dead_event_has_correct_structure(): void
    {
        $event = new PeppolTransmissionDead(
            $this->transmission,
            'Max retries exceeded'
        );

        $this->assertEquals($this->transmission->id, $event->transmission->id);
        $this->assertEquals('Max retries exceeded', $event->reason);
        $this->assertEquals('PeppolTransmissionDead', $event->getEventName());

        $payload = $event->getAuditPayload();
        $this->assertArrayHasKey('reason', $payload);
    }

    #[Test]
    public function events_implement_peppol_event_interface(): void
    {
        $events = [
            new PeppolIntegrationCreated($this->integration),
            new PeppolIntegrationTested($this->integration, true, 'test'),
            new PeppolTransmissionCreated($this->transmission),
            new PeppolTransmissionSent($this->transmission, 'EXT-123'),
            new PeppolTransmissionFailed($this->transmission, 'error', 'type'),
            new PeppolTransmissionDead($this->transmission, 'reason'),
        ];

        foreach ($events as $event) {
            $this->assertIsString($event->getEventName());
            $this->assertIsArray($event->getAuditPayload());
        }
    }

    #[Test]
    public function events_can_be_serialized_for_queuing(): void
    {
        $event = new PeppolTransmissionCreated($this->transmission);
        
        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(PeppolTransmissionCreated::class, $unserialized);
        $this->assertEquals($event->transmission->id, $unserialized->transmission->id);
    }
}