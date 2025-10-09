<?php

namespace Modules\Invoices\Jobs\Peppol;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Invoices\Events\Peppol\PeppolTransmissionCreated;
use Modules\Invoices\Events\Peppol\PeppolTransmissionFailed;
use Modules\Invoices\Events\Peppol\PeppolTransmissionPrepared;
use Modules\Invoices\Events\Peppol\PeppolTransmissionSent;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Peppol\FormatHandlers\FormatHandlerFactory;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Peppol\Services\PeppolTransformerService;

/**
 * Job to send an invoice to the Peppol network
 * 
 * This is the main job that orchestrates the entire sending process:
 * 1. Pre-send validation
 * 2. Create/find transmission record
 * 3. Transform invoice to Peppol format
 * 4. Generate and store XML/PDF files
 * 5. Send to provider
 * 6. Handle response and schedule retries if needed
 */
class SendInvoiceToPeppolJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Invoice $invoice;
    public PeppolIntegration $integration;
    public bool $force;
    public ?int $transmissionId;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    public function __construct(Invoice $invoice, PeppolIntegration $integration, bool $force = false, ?int $transmissionId = null)
    {
        $this->invoice = $invoice;
        $this->integration = $integration;
        $this->force = $force;
        $this->transmissionId = $transmissionId;
    }

    public function handle(): void
    {
        try {
            Log::info('Starting Peppol invoice sending job', [
                'invoice_id' => $this->invoice->id,
                'integration_id' => $this->integration->id,
            ]);

            // Step 1: Pre-send validation
            $this->validateInvoice();

            // Step 2: Create or retrieve transmission record
            $transmission = $this->getOrCreateTransmission();

            // If transmission is already in a final state and not forcing, skip
            if (!$this->force && $transmission->isFinal()) {
                Log::info('Transmission already in final state, skipping', [
                    'transmission_id' => $transmission->id,
                    'status' => $transmission->status,
                ]);
                return;
            }

            // Step 3: Mark as processing
            $transmission->update(['status' => PeppolTransmission::STATUS_PROCESSING]);

            // Step 4: Transform and generate files
            $this->prepareArtifacts($transmission);
            event(new PeppolTransmissionPrepared($transmission));

            // Step 5: Send to provider
            $this->sendToProvider($transmission);

        } catch (\Exception $e) {
            Log::error('Peppol sending job failed', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($transmission)) {
                $this->handleFailure($transmission, $e);
            }

            throw $e;
        }
    }

    /**
     * Validate that the invoice can be sent
     */
    protected function validateInvoice(): void
    {
        if (!$this->invoice->customer) {
            throw new \InvalidArgumentException('Invoice must have a customer');
        }

        if (!$this->invoice->customer->enable_e_invoicing) {
            throw new \InvalidArgumentException('Customer does not have e-invoicing enabled');
        }

        if (!$this->invoice->customer->hasPeppolIdValidated()) {
            throw new \InvalidArgumentException('Customer Peppol ID has not been validated');
        }

        if (!$this->invoice->number) {
            throw new \InvalidArgumentException('Invoice must have an invoice number');
        }

        if ($this->invoice->invoiceItems->count() === 0) {
            throw new \InvalidArgumentException('Invoice must have at least one line item');
        }
    }

    /**
     * Get existing transmission or create new one
     */
    protected function getOrCreateTransmission(): PeppolTransmission
    {
        // If transmission ID provided, use that
        if ($this->transmissionId) {
            return PeppolTransmission::findOrFail($this->transmissionId);
        }

        // Calculate idempotency key
        $idempotencyKey = $this->calculateIdempotencyKey();

        // Try to find existing transmission
        $transmission = PeppolTransmission::where('idempotency_key', $idempotencyKey)->first();

        if ($transmission) {
            Log::info('Found existing transmission', ['transmission_id' => $transmission->id]);
            return $transmission;
        }

        // Create new transmission
        $transmission = PeppolTransmission::create([
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->invoice->customer_id,
            'integration_id' => $this->integration->id,
            'format' => $this->determineFormat(),
            'status' => PeppolTransmission::STATUS_PENDING,
            'idempotency_key' => $idempotencyKey,
            'attempts' => 0,
        ]);

        event(new PeppolTransmissionCreated($transmission));

        return $transmission;
    }

    /**
     * Calculate idempotency key to prevent duplicate transmissions
     */
    protected function calculateIdempotencyKey(): string
    {
        return hash('sha256', implode('|', [
            $this->invoice->id,
            $this->invoice->customer->peppol_id,
            $this->integration->id,
            $this->invoice->updated_at->timestamp,
        ]));
    }

    /**
     * Determine which format to use
     */
    protected function determineFormat(): string
    {
        return $this->invoice->customer->peppol_format ?? config('invoices.peppol.default_format', 'peppol_bis_3.0');
    }

    /**
     * Transform invoice and generate XML/PDF artifacts
     */
    protected function prepareArtifacts(PeppolTransmission $transmission): void
    {
        // Get format handler
        $handler = FormatHandlerFactory::make($transmission->format);

        // Generate XML directly from invoice using handler
        $xml = $handler->generateXml($this->invoice);

        // Validate XML (handler's validate method checks the invoice)
        $errors = $handler->validate($this->invoice);
        if (!empty($errors)) {
            throw new \RuntimeException('Invoice validation failed: ' . implode(', ', $errors));
        }

        // Store XML
        $xmlPath = $this->storeXml($transmission, $xml);

        // Generate/get PDF
        $pdfPath = $this->storePdf($transmission);

        // Update transmission with paths
        $transmission->update([
            'stored_xml_path' => $xmlPath,
            'stored_pdf_path' => $pdfPath,
        ]);
    }

    /**
     * Store XML file
     */
    protected function storeXml(PeppolTransmission $transmission, string $xml): string
    {
        $path = sprintf(
            'peppol/%d/%d/%d/%s/invoice.xml',
            $this->integration->id,
            now()->year,
            now()->month,
            $transmission->id
        );

        Storage::put($path, $xml);

        return $path;
    }

    /**
     * Store PDF file
     */
    protected function storePdf(PeppolTransmission $transmission): string
    {
        $path = sprintf(
            'peppol/%d/%d/%d/%s/invoice.pdf',
            $this->integration->id,
            now()->year,
            now()->month,
            $transmission->id
        );

        // Generate PDF from invoice
        // TODO: Implement PDF generation
        $pdfContent = ''; // Placeholder

        Storage::put($path, $pdfContent);

        return $path;
    }

    /**
     * Send to Peppol provider
     */
    protected function sendToProvider(PeppolTransmission $transmission): void
    {
        $provider = ProviderFactory::make($this->integration);

        // Get XML content
        $xml = Storage::get($transmission->stored_xml_path);

        // Prepare transmission data
        $transmissionData = [
            'transmission_id' => $transmission->id,
            'invoice_id' => $this->invoice->id,
            'customer_peppol_id' => $this->invoice->customer->peppol_id,
            'customer_peppol_scheme' => $this->invoice->customer->peppol_scheme,
            'format' => $transmission->format,
            'xml' => $xml,
            'idempotency_key' => $transmission->idempotency_key,
        ];

        // Send to provider
        $result = $provider->sendInvoice($transmissionData);

        // Handle result
        if ($result['accepted']) {
            $transmission->markAsSent($result['external_id']);
            $transmission->update(['provider_response' => $result['response']]);
            
            event(new PeppolTransmissionSent($transmission));
            
            Log::info('Invoice sent to Peppol successfully', [
                'transmission_id' => $transmission->id,
                'external_id' => $result['external_id'],
            ]);
        } else {
            // Provider rejected the submission
            $errorType = $provider->classifyError($result['status_code'], $result['response']);
            
            $transmission->markAsFailed($result['message'], $errorType);
            $transmission->update(['provider_response' => $result['response']]);
            
            event(new PeppolTransmissionFailed($transmission, $result['message']));
            
            // Schedule retry if transient error
            if ($errorType === PeppolTransmission::ERROR_TRANSIENT) {
                $this->scheduleRetry($transmission);
            }
        }
    }

    /**
     * Handle job failure
     */
    protected function handleFailure(PeppolTransmission $transmission, \Exception $e): void
    {
        $transmission->markAsFailed(
            $e->getMessage(),
            PeppolTransmission::ERROR_UNKNOWN
        );

        event(new PeppolTransmissionFailed($transmission, $e->getMessage()));

        // Schedule retry for unknown errors
        $this->scheduleRetry($transmission);
    }

    /**
     * Schedule a retry with exponential backoff
     */
    protected function scheduleRetry(PeppolTransmission $transmission): void
    {
        $maxAttempts = config('invoices.peppol.max_retry_attempts', 5);

        if ($transmission->attempts >= $maxAttempts) {
            $transmission->markAsDead('Maximum retry attempts exceeded');
            return;
        }

        // Exponential backoff: 1min, 5min, 30min, 2h, 6h
        $delays = [60, 300, 1800, 7200, 21600];
        $delay = $delays[$transmission->attempts] ?? 21600;

        $nextRetryAt = now()->addSeconds($delay);
        $transmission->scheduleRetry($nextRetryAt);

        // Re-dispatch the job
        static::dispatch($this->invoice, $this->integration, false, $transmission->id)
            ->delay($nextRetryAt);

        Log::info('Scheduled retry for Peppol transmission', [
            'transmission_id' => $transmission->id,
            'attempt' => $transmission->attempts,
            'next_retry_at' => $nextRetryAt,
        ]);
    }
}
