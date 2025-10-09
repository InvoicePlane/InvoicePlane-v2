<?php

namespace Modules\Invoices\Tests\Unit\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\PeppolErrorType;
use Modules\Invoices\Enums\PeppolTransmissionStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Models\PeppolTransmissionResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PeppolTransmissionTest - Unit tests for PeppolTransmission model.
 *
 * Tests model relationships, status transitions, and data management.
 *
 * @package Modules\Invoices\Tests\Unit\Models
 */
class PeppolTransmissionTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected PeppolIntegration $integration;
    protected Invoice $invoice;
    protected Relation $customer;
    protected PeppolTransmission $transmission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        
        $this->integration = PeppolIntegration::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->customer = Relation::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
        ]);

        $this->transmission = PeppolTransmission::create([
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'integration_id' => $this->integration->id,
            'format' => 'peppol_bis_3.0',
            'status' => PeppolTransmissionStatus::PENDING,
            'attempts' => 0,
            'idempotency_key' => 'test-key-123',
        ]);
    }

    #[Test]
    public function it_can_be_created_with_required_fields(): void
    {
        $transmission = PeppolTransmission::create([
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'integration_id' => $this->integration->id,
            'format' => 'ubl_2.1',
            'status' => PeppolTransmissionStatus::PENDING,
            'attempts' => 0,
            'idempotency_key' => 'unique-key',
        ]);

        $this->assertInstanceOf(PeppolTransmission::class, $transmission);
        $this->assertEquals($this->invoice->id, $transmission->invoice_id);
        $this->assertEquals('ubl_2.1', $transmission->format);
        $this->assertEquals(PeppolTransmissionStatus::PENDING, $transmission->status);
    }

    #[Test]
    public function it_belongs_to_an_invoice(): void
    {
        $this->assertInstanceOf(Invoice::class, $this->transmission->invoice);
        $this->assertEquals($this->invoice->id, $this->transmission->invoice->id);
    }

    #[Test]
    public function it_belongs_to_a_customer(): void
    {
        $this->assertInstanceOf(Relation::class, $this->transmission->customer);
        $this->assertEquals($this->customer->id, $this->transmission->customer->id);
    }

    #[Test]
    public function it_belongs_to_an_integration(): void
    {
        $this->assertInstanceOf(PeppolIntegration::class, $this->transmission->integration);
        $this->assertEquals($this->integration->id, $this->transmission->integration->id);
    }

    #[Test]
    public function it_has_many_responses(): void
    {
        $response = PeppolTransmissionResponse::create([
            'transmission_id' => $this->transmission->id,
            'response_key' => 'external_id',
            'response_value' => 'EXT-12345',
        ]);

        $this->assertTrue($this->transmission->responses()->exists());
        $this->assertEquals($response->id, $this->transmission->responses->first()->id);
    }

    #[Test]
    public function it_casts_status_to_enum(): void
    {
        $this->transmission->status = PeppolTransmissionStatus::SENT;
        $this->transmission->save();

        $retrieved = PeppolTransmission::find($this->transmission->id);
        
        $this->assertInstanceOf(PeppolTransmissionStatus::class, $retrieved->status);
        $this->assertEquals(PeppolTransmissionStatus::SENT, $retrieved->status);
    }

    #[Test]
    public function it_casts_error_type_to_enum(): void
    {
        $this->transmission->error_type = PeppolErrorType::VALIDATION_ERROR;
        $this->transmission->save();

        $retrieved = PeppolTransmission::find($this->transmission->id);
        
        $this->assertInstanceOf(PeppolErrorType::class, $retrieved->error_type);
        $this->assertEquals(PeppolErrorType::VALIDATION_ERROR, $retrieved->error_type);
    }

    #[Test]
    public function it_casts_datetime_fields(): void
    {
        $now = Carbon::now();
        
        $this->transmission->sent_at = $now;
        $this->transmission->acknowledged_at = $now;
        $this->transmission->next_retry_at = $now;
        $this->transmission->save();

        $retrieved = PeppolTransmission::find($this->transmission->id);
        
        $this->assertInstanceOf(Carbon::class, $retrieved->sent_at);
        $this->assertInstanceOf(Carbon::class, $retrieved->acknowledged_at);
        $this->assertInstanceOf(Carbon::class, $retrieved->next_retry_at);
    }

    #[Test]
    public function it_increments_attempts_counter(): void
    {
        $this->assertEquals(0, $this->transmission->attempts);

        $this->transmission->attempts++;
        $this->transmission->save();

        $this->assertEquals(1, $this->transmission->fresh()->attempts);
    }

    #[Test]
    public function it_stores_external_id(): void
    {
        $externalId = 'PROVIDER-DOC-12345';
        
        $this->transmission->external_id = $externalId;
        $this->transmission->save();

        $this->assertEquals($externalId, $this->transmission->fresh()->external_id);
    }

    #[Test]
    public function it_stores_file_paths(): void
    {
        $this->transmission->stored_xml_path = 'peppol/xml/invoice-123.xml';
        $this->transmission->stored_pdf_path = 'peppol/pdf/invoice-123.pdf';
        $this->transmission->save();

        $retrieved = $this->transmission->fresh();
        $this->assertEquals('peppol/xml/invoice-123.xml', $retrieved->stored_xml_path);
        $this->assertEquals('peppol/pdf/invoice-123.pdf', $retrieved->stored_pdf_path);
    }

    #[Test]
    public function it_stores_error_information(): void
    {
        $this->transmission->last_error = 'Connection timeout';
        $this->transmission->error_type = PeppolErrorType::NETWORK_ERROR;
        $this->transmission->save();

        $retrieved = $this->transmission->fresh();
        $this->assertEquals('Connection timeout', $retrieved->last_error);
        $this->assertEquals(PeppolErrorType::NETWORK_ERROR, $retrieved->error_type);
    }

    #[Test]
    public function it_can_get_provider_response_as_array(): void
    {
        PeppolTransmissionResponse::create([
            'transmission_id' => $this->transmission->id,
            'response_key' => 'document_id',
            'response_value' => 'DOC-123',
        ]);

        PeppolTransmissionResponse::create([
            'transmission_id' => $this->transmission->id,
            'response_key' => 'status',
            'response_value' => 'submitted',
        ]);

        $this->transmission->refresh();
        $response = $this->transmission->provider_response;

        $this->assertIsArray($response);
        $this->assertEquals('DOC-123', $response['document_id']);
        $this->assertEquals('submitted', $response['status']);
    }

    #[Test]
    public function provider_response_returns_empty_array_when_no_responses(): void
    {
        $response = $this->transmission->provider_response;

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    #[Test]
    public function it_handles_null_error_type(): void
    {
        $this->transmission->error_type = null;
        $this->transmission->save();

        $this->assertNull($this->transmission->fresh()->error_type);
    }

    #[Test]
    public function it_handles_null_datetime_fields(): void
    {
        $this->assertNull($this->transmission->sent_at);
        $this->assertNull($this->transmission->acknowledged_at);
        $this->assertNull($this->transmission->next_retry_at);
    }

    #[Test]
    public function idempotency_key_ensures_uniqueness(): void
    {
        $this->expectException(\Exception::class);

        // Try to create duplicate with same idempotency key
        PeppolTransmission::create([
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'integration_id' => $this->integration->id,
            'format' => 'peppol_bis_3.0',
            'status' => PeppolTransmissionStatus::PENDING,
            'attempts' => 0,
            'idempotency_key' => 'test-key-123', // Same as in setUp
        ]);
    }

    #[Test]
    public function it_can_have_multiple_responses(): void
    {
        $responses = [
            ['response_key' => 'key1', 'response_value' => 'value1'],
            ['response_key' => 'key2', 'response_value' => 'value2'],
            ['response_key' => 'key3', 'response_value' => 'value3'],
        ];

        foreach ($responses as $response) {
            PeppolTransmissionResponse::create(array_merge($response, [
                'transmission_id' => $this->transmission->id,
            ]));
        }

        $this->assertEquals(3, $this->transmission->responses()->count());
    }

    #[Test]
    public function it_tracks_timestamps(): void
    {
        $this->assertInstanceOf(Carbon::class, $this->transmission->created_at);
        $this->assertInstanceOf(Carbon::class, $this->transmission->updated_at);
    }

    #[Test]
    public function it_updates_updated_at_on_save(): void
    {
        $originalUpdatedAt = $this->transmission->updated_at;
        
        sleep(1);
        $this->transmission->status = PeppolTransmissionStatus::PROCESSING;
        $this->transmission->save();

        $this->assertTrue($this->transmission->updated_at->isAfter($originalUpdatedAt));
    }
}