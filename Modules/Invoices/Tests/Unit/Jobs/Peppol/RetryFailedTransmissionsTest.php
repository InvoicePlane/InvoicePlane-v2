<?php

namespace Modules\Invoices\Tests\Unit\Jobs\Peppol;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\PeppolTransmissionStatus;
use Modules\Invoices\Events\Peppol\PeppolTransmissionDead;
use Modules\Invoices\Jobs\Peppol\RetryFailedTransmissions;
use Modules\Invoices\Jobs\Peppol\SendInvoiceToPeppolJob;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * RetryFailedTransmissionsTest - Unit tests for RetryFailedTransmissions job.
 *
 * Tests retry logic, backoff handling, and dead letter queue processing.
 *
 * @package Modules\Invoices\Tests\Unit\Jobs\Peppol
 */
class RetryFailedTransmissionsTest extends TestCase
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

        Config::set('invoices.peppol.max_retry_attempts', 5);
    }

    #[Test]
    public function it_retries_transmissions_that_are_due_for_retry(): void
    {
        Bus::fake();

        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5), // Due for retry
            'attempts' => 2,
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        Bus::assertDispatched(SendInvoiceToPeppolJob::class, function ($job) use ($transmission) {
            return $job->invoice->id === $this->invoice->id
                && $job->integration->id === $this->integration->id
                && $job->transmissionId === $transmission->id;
        });
    }

    #[Test]
    public function it_does_not_retry_transmissions_not_yet_due(): void
    {
        Bus::fake();

        PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->addMinutes(10), // Not yet due
            'attempts' => 2,
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        Bus::assertNotDispatched(SendInvoiceToPeppolJob::class);
    }

    #[Test]
    public function it_marks_transmission_as_dead_when_max_attempts_reached(): void
    {
        Event::fake();
        Bus::fake();

        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 5, // At max attempts
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        $transmission->refresh();
        $this->assertEquals(PeppolTransmissionStatus::DEAD, $transmission->status);
        $this->assertStringContainsString('Maximum retry attempts exceeded', $transmission->last_error);

        Event::assertDispatched(PeppolTransmissionDead::class);
        Bus::assertNotDispatched(SendInvoiceToPeppolJob::class);
    }

    #[Test]
    public function it_marks_transmission_as_dead_when_exceeding_max_attempts(): void
    {
        Event::fake();

        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 6, // Exceeds max
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        $transmission->refresh();
        $this->assertEquals(PeppolTransmissionStatus::DEAD, $transmission->status);
    }

    #[Test]
    public function it_processes_multiple_due_transmissions(): void
    {
        Bus::fake();

        for ($i = 0; $i < 5; $i++) {
            PeppolTransmission::factory()->create([
                'integration_id' => $this->integration->id,
                'invoice_id' => $this->invoice->id,
                'status' => PeppolTransmissionStatus::RETRYING,
                'next_retry_at' => now()->subMinutes(5),
                'attempts' => 2,
            ]);
        }

        $job = new RetryFailedTransmissions();
        $job->handle();

        Bus::assertDispatched(SendInvoiceToPeppolJob::class, 5);
    }

    #[Test]
    public function it_continues_processing_after_individual_failure(): void
    {
        Bus::fake();

        $transmission1 = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 2,
        ]);

        // Create a second valid transmission
        $transmission2 = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 2,
        ]);

        // Make first one throw an exception by deleting the invoice
        $transmission1->invoice()->delete();

        $job = new RetryFailedTransmissions();
        $job->handle();

        // Should still process the second transmission
        Bus::assertDispatched(SendInvoiceToPeppolJob::class, function ($job) use ($transmission2) {
            return $job->transmissionId === $transmission2->id;
        });
    }

    #[Test]
    public function it_processes_maximum_of_50_transmissions_per_batch(): void
    {
        Bus::fake();

        // Create more than 50 transmissions
        for ($i = 0; $i < 75; $i++) {
            PeppolTransmission::factory()->create([
                'integration_id' => $this->integration->id,
                'invoice_id' => $this->invoice->id,
                'status' => PeppolTransmissionStatus::RETRYING,
                'next_retry_at' => now()->subMinutes(5),
                'attempts' => 2,
            ]);
        }

        $job = new RetryFailedTransmissions();
        $job->handle();

        // Should only process 50
        Bus::assertDispatched(SendInvoiceToPeppolJob::class, 50);
    }

    #[Test]
    public function it_only_processes_transmissions_with_retrying_status(): void
    {
        Bus::fake();

        // Create transmissions with various statuses
        PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::PENDING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 2,
        ]);

        PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::FAILED,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 2,
        ]);

        PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 2,
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        // Only the RETRYING one should be dispatched
        Bus::assertDispatched(SendInvoiceToPeppolJob::class, 1);
    }

    #[Test]
    public function it_respects_configured_max_retry_attempts(): void
    {
        Event::fake();
        Config::set('invoices.peppol.max_retry_attempts', 3);

        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 3, // At configured max
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        $transmission->refresh();
        $this->assertEquals(PeppolTransmissionStatus::DEAD, $transmission->status);
    }

    #[Test]
    public function it_logs_transmission_marked_as_dead(): void
    {
        Event::fake();

        $transmission = PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 5,
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        Event::assertDispatched(PeppolTransmissionDead::class, function ($event) use ($transmission) {
            return $event->transmission->id === $transmission->id
                && $event->reason === 'Maximum retry attempts exceeded';
        });
    }

    #[Test]
    public function it_dispatches_job_without_force_flag(): void
    {
        Bus::fake();

        PeppolTransmission::factory()->create([
            'integration_id' => $this->integration->id,
            'invoice_id' => $this->invoice->id,
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => now()->subMinutes(5),
            'attempts' => 2,
        ]);

        $job = new RetryFailedTransmissions();
        $job->handle();

        Bus::assertDispatched(SendInvoiceToPeppolJob::class, function ($job) {
            return $job->force === false;
        });
    }
}