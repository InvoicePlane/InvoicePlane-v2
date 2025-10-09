<?php

namespace Modules\Invoices\Tests\Unit\Listeners\Peppol;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Company;
use Modules\Invoices\Events\Peppol\PeppolTransmissionCreated;
use Modules\Invoices\Events\Peppol\PeppolTransmissionFailed;
use Modules\Invoices\Events\Peppol\PeppolIntegrationCreated;
use Modules\Invoices\Listeners\Peppol\LogPeppolEventToAudit;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LogPeppolEventToAuditTest - Unit tests for LogPeppolEventToAudit listener.
 *
 * Tests audit log creation for Peppol events.
 *
 * @package Modules\Invoices\Tests\Unit\Listeners\Peppol
 */
class LogPeppolEventToAuditTest extends TestCase
{
    use RefreshDatabase;

    protected LogPeppolEventToAudit $listener;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new LogPeppolEventToAudit();
        $this->company = Company::factory()->create();
    }

    #[Test]
    public function it_creates_audit_log_for_transmission_event(): void
    {
        $integration = PeppolIntegration::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $integration->id,
            'invoice_id' => $invoice->id,
        ]);

        $event = new PeppolTransmissionCreated($transmission);
        
        $this->listener->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'audit_id' => $transmission->id,
            'audit_type' => 'peppol_transmission',
            'activity' => 'PeppolTransmissionCreated',
        ]);
    }

    #[Test]
    public function it_creates_audit_log_for_integration_event(): void
    {
        $integration = PeppolIntegration::factory()->create(['company_id' => $this->company->id]);

        $event = new PeppolIntegrationCreated($integration);
        
        $this->listener->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'audit_id' => $integration->id,
            'audit_type' => 'peppol_integration',
            'activity' => 'PeppolIntegrationCreated',
        ]);
    }

    #[Test]
    public function it_stores_event_payload_as_json(): void
    {
        $integration = PeppolIntegration::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $integration->id,
            'invoice_id' => $invoice->id,
        ]);

        $event = new PeppolTransmissionFailed(
            $transmission,
            'Connection timeout',
            'network'
        );
        
        $this->listener->handle($event);

        $auditLog = AuditLog::where('audit_id', $transmission->id)->first();
        $this->assertNotNull($auditLog);

        $info = json_decode($auditLog->info, true);
        $this->assertIsArray($info);
        $this->assertArrayHasKey('error', $info);
        $this->assertEquals('Connection timeout', $info['error']);
    }

    #[Test]
    public function it_determines_correct_audit_type_for_transmission_events(): void
    {
        $integration = PeppolIntegration::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $integration->id,
            'invoice_id' => $invoice->id,
        ]);

        $event = new PeppolTransmissionCreated($transmission);
        
        $this->listener->handle($event);

        $auditLog = AuditLog::where('audit_id', $transmission->id)->first();
        $this->assertEquals('peppol_transmission', $auditLog->audit_type);
    }

    #[Test]
    public function it_logs_multiple_events_from_same_entity(): void
    {
        $integration = PeppolIntegration::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create(['company_id' => $this->company->id]);
        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $integration->id,
            'invoice_id' => $invoice->id,
        ]);

        // Log multiple events
        $this->listener->handle(new PeppolTransmissionCreated($transmission));
        $this->listener->handle(new PeppolTransmissionFailed($transmission, 'error', 'type'));

        $logs = AuditLog::where('audit_id', $transmission->id)->get();
        $this->assertCount(2, $logs);
    }
}