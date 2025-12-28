<?php

namespace Modules\Invoices\Jobs\Peppol;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Invoices\Enums\PeppolErrorType;
use Modules\Invoices\Enums\PeppolTransmissionStatus;
use Modules\Invoices\Events\Peppol\PeppolTransmissionCreated;
use Modules\Invoices\Events\Peppol\PeppolTransmissionFailed;
use Modules\Invoices\Events\Peppol\PeppolTransmissionPrepared;
use Modules\Invoices\Events\Peppol\PeppolTransmissionSent;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Peppol\FormatHandlers\FormatHandlerFactory;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Traits\LogsPeppolActivity;
use RuntimeException;

/**
 * Job to send an invoice to the Peppol network.
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
    use Dispatchable;
    use InteractsWithQueue;
    use LogsPeppolActivity;
    use Queueable;
    use SerializesModels;

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

    /**
     * Create a job to send a specific invoice to the Peppol network.
     *
     * @param Invoice           $invoice        the invoice to be transmitted
     * @param PeppolIntegration $integration    the Peppol integration context used for transmission
     * @param bool              $force          whether to force processing even if a final transmission already exists
     * @param int|null          $transmissionId optional specific PeppolTransmission ID to use instead of locating or creating one
     */
    public function __construct(Invoice $invoice, PeppolIntegration $integration, bool $force = false, ?int $transmissionId = null)
    {
        $this->invoice        = $invoice;
        $this->integration    = $integration;
        $this->force          = $force;
        $this->transmissionId = $transmissionId;
    }

    /**
     * Coordinates sending the invoice to the Peppol network as a queued job.
     *
     * Validates the invoice, obtains or creates a PeppolTransmission, updates its status
     * to processing, generates and stores XML/PDF artifacts, fires a prepared event,
     * and submits the transmission to the configured provider. On error, logs the failure
     * and delegates failure handling (including marking the transmission and scheduling retries).
     */
    public function handle(): void
    {
        try {
            $this->logPeppolInfo('Starting Peppol invoice sending job', [
                'invoice_id'     => $this->invoice->id,
                'integration_id' => $this->integration->id,
            ]);

            // Step 1: Pre-send validation
            $this->validateInvoice();

            // Step 2: Create or retrieve transmission record
            $transmission = $this->getOrCreateTransmission();

            // If transmission is already in a final state and not forcing, skip
            if ( ! $this->force && $transmission->isFinal()) {
                $this->logPeppolInfo('Transmission already in final state, skipping', [
                    'transmission_id' => $transmission->id,
                    'status'          => $transmission->status->value,
                ]);

                return;
            }

            // Step 3: Mark as processing
            $transmission->update(['status' => PeppolTransmissionStatus::PROCESSING]);

            // Step 4: Transform and generate files
            $this->prepareArtifacts($transmission);
            event(new PeppolTransmissionPrepared($transmission));

            // Step 5: Send to provider
            $this->sendToProvider($transmission);
        } catch (Exception $e) {
            $this->logPeppolError('Peppol sending job failed', [
                'invoice_id' => $this->invoice->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            if (isset($transmission)) {
                $this->handleFailure($transmission, $e);
            }

            throw $e;
        }
    }

    /**
     * Ensure the invoice meets all prerequisites for Peppol transmission.
     *
     * Validations:
     * - Invoice must belong to a customer.
     * - Customer must have e-invoicing enabled.
     * - Customer's Peppol ID must be validated.
     * - Invoice must have an invoice number.
     * - Invoice must contain at least one line item.
     *
     * @throws InvalidArgumentException if any validation fails
     */
    protected function validateInvoice(): void
    {
        if ( ! $this->invoice->customer) {
            throw new InvalidArgumentException('Invoice must have a customer');
        }

        if ( ! $this->invoice->customer->enable_e_invoicing) {
            throw new InvalidArgumentException('Customer does not have e-invoicing enabled');
        }

        if ( ! $this->invoice->customer->hasPeppolIdValidated()) {
            throw new InvalidArgumentException('Customer Peppol ID has not been validated');
        }

        if ( ! $this->invoice->number) {
            throw new InvalidArgumentException('Invoice must have an invoice number');
        }

        if ($this->invoice->invoiceItems->count() === 0) {
            throw new InvalidArgumentException('Invoice must have at least one line item');
        }
    }

    /**
     * Retrieve an existing PeppolTransmission by idempotency key or transmission ID, or create and persist a new pending transmission.
     *
     * When a new transmission is created this method persists the record and emits a PeppolTransmissionCreated event.
     *
     * @return PeppolTransmission the existing or newly created transmission
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if a specific transmission ID was provided but no record is found
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
            $this->logPeppolInfo('Found existing transmission', ['transmission_id' => $transmission->id]);

            return $transmission;
        }

        // Create new transmission
        $transmission = PeppolTransmission::create([
            'invoice_id'      => $this->invoice->id,
            'customer_id'     => $this->invoice->customer_id,
            'integration_id'  => $this->integration->id,
            'format'          => $this->determineFormat(),
            'status'          => PeppolTransmissionStatus::PENDING,
            'idempotency_key' => $idempotencyKey,
            'attempts'        => 0,
        ]);

        event(new PeppolTransmissionCreated($transmission));

        return $transmission;
    }

    /**
     * Produce an idempotency key for the invoice transmission.
     *
     * The key is derived from the invoice ID, the customer's Peppol ID, the
     * integration ID, and the invoice's updated-at timestamp to uniquely
     * identify a transmission attempt.
     *
     * @return string a SHA-256 hash string computed from the invoice ID, customer Peppol ID, integration ID, and invoice updated timestamp
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
     * Selects the Peppol document format to use for this invoice transmission.
     *
     * Prefers the customer's configured `peppol_format`; if absent, falls back to the application default (configured `invoices.peppol.default_format` or `'peppol_bis_3.0'`).
     *
     * @return string the Peppol format identifier to use for the transmission
     */
    protected function determineFormat(): string
    {
        return $this->invoice->customer->peppol_format ?? config('invoices.peppol.default_format', 'peppol_bis_3.0');
    }

    /**
     * Prepare and persist Peppol XML and PDF artifacts for the given transmission.
     *
     * Generates and validates the XML for the job's invoice, stores the XML and a PDF to storage,
     * and updates the transmission with the resulting storage paths.
     *
     * @param PeppolTransmission $transmission the transmission to associate the stored artifact paths with
     *
     * @throws RuntimeException if invoice validation fails; the exception message contains the validation errors
     */
    protected function prepareArtifacts(PeppolTransmission $transmission): void
    {
        // Get format handler
        $handler = FormatHandlerFactory::make($transmission->format);

        // Generate XML directly from invoice using handler
        $xml = $handler->generateXml($this->invoice);

        // Validate XML (handler's validate method checks the invoice)
        $errors = $handler->validate($this->invoice);
        if ( ! empty($errors)) {
            throw new RuntimeException('Invoice validation failed: ' . implode(', ', $errors));
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
     * Persist the generated Peppol XML for a transmission to storage.
     *
     * @param PeppolTransmission $transmission the transmission record used to construct the storage path
     * @param string             $xml          the XML content to store
     *
     * @return string the storage path where the XML was saved
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
     * Persist a PDF representation of the invoice for the given Peppol transmission and return its storage path.
     *
     * @param PeppolTransmission $transmission the transmission used to build the storage path
     *
     * @return string the storage path where the PDF was saved
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
     * Submits the prepared invoice XML to the configured Peppol provider and updates the transmission state.
     *
     * On success, marks the transmission as sent, stores the provider response, and emits PeppolTransmissionSent.
     * On failure, marks the transmission as failed, stores the provider response, emits PeppolTransmissionFailed, and schedules a retry when the error is classified as transient.
     *
     * @param PeppolTransmission $transmission the transmission record representing this send attempt
     */
    protected function sendToProvider(PeppolTransmission $transmission): void
    {
        $provider = ProviderFactory::make($this->integration);

        // Get XML content
        $xml = Storage::get($transmission->stored_xml_path);

        // Prepare transmission data
        $transmissionData = [
            'transmission_id'        => $transmission->id,
            'invoice_id'             => $this->invoice->id,
            'customer_peppol_id'     => $this->invoice->customer->peppol_id,
            'customer_peppol_scheme' => $this->invoice->customer->peppol_scheme,
            'format'                 => $transmission->format,
            'xml'                    => $xml,
            'idempotency_key'        => $transmission->idempotency_key,
        ];

        // Send to provider
        $result = $provider->sendInvoice($transmissionData);

        // Handle result
        if ($result['accepted']) {
            $transmission->markAsSent($result['external_id']);
            $transmission->setProviderResponse($result['response'] ?? []);

            event(new PeppolTransmissionSent($transmission));

            $this->logPeppolInfo('Invoice sent to Peppol successfully', [
                'transmission_id' => $transmission->id,
                'external_id'     => $result['external_id'],
            ]);
        } else {
            // Provider rejected the submission
            $errorType = $this->classifyError($result['status_code'], $result['response']);

            $transmission->markAsFailed($result['message'], $errorType);
            $transmission->setProviderResponse($result['response'] ?? []);

            event(new PeppolTransmissionFailed($transmission, $result['message']));

            // Schedule retry if transient error
            if ($errorType === PeppolErrorType::TRANSIENT) {
                $this->scheduleRetry($transmission);
            }
        }
    }

    /**
     * Determine the Peppol error type corresponding to an HTTP status code.
     *
     * @param int        $statusCode   HTTP status code from the provider response
     * @param array|null $responseBody optional response body returned by the provider; currently not used for classification
     *
     * @return peppolErrorType `TRANSIENT` for 5xx, 429 or 408 status codes; `PERMANENT` for 401, 403, 404, 400 or 422; `UNKNOWN` otherwise
     */
    protected function classifyError(int $statusCode, ?array $responseBody = null): PeppolErrorType
    {
        return match(true) {
            $statusCode >= 500                         => PeppolErrorType::TRANSIENT,
            $statusCode === 429                        => PeppolErrorType::TRANSIENT,
            $statusCode === 408                        => PeppolErrorType::TRANSIENT,
            $statusCode === 401 || $statusCode === 403 => PeppolErrorType::PERMANENT,
            $statusCode === 404                        => PeppolErrorType::PERMANENT,
            $statusCode === 400 || $statusCode === 422 => PeppolErrorType::PERMANENT,
            default                                    => PeppolErrorType::UNKNOWN,
        };
    }

    /**
     * Mark the given transmission as failed because of an exception, emit a failure event, and schedule a retry if appropriate.
     *
     * @param PeppolTransmission $transmission the transmission to mark as failed
     * @param Exception          $e            the exception that caused the failure; its message is recorded on the transmission
     */
    protected function handleFailure(PeppolTransmission $transmission, Exception $e): void
    {
        $transmission->markAsFailed(
            $e->getMessage(),
            PeppolErrorType::UNKNOWN
        );

        event(new PeppolTransmissionFailed($transmission, $e->getMessage()));

        // Schedule retry for unknown errors
        $this->scheduleRetry($transmission);
    }

    /**
     * Schedule the transmission for a retry using exponential backoff.
     *
     * If the transmission has reached the maximum configured attempts, marks it as dead.
     * Otherwise computes the next retry time using increasing delays, updates the transmission's
     * retry schedule, re-dispatches this job with the computed delay, and logs the scheduling.
     *
     * @param PeppolTransmission $transmission the transmission to schedule a retry for
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
        $delay  = $delays[$transmission->attempts] ?? 21600;

        $nextRetryAt = now()->addSeconds($delay);
        $transmission->scheduleRetry($nextRetryAt);

        // Re-dispatch the job
        static::dispatch($this->invoice, $this->integration, false, $transmission->id)
            ->delay($nextRetryAt);

        $this->logPeppolInfo('Scheduled retry for Peppol transmission', [
            'transmission_id' => $transmission->id,
            'attempt'         => $transmission->attempts,
            'next_retry_at'   => $nextRetryAt,
        ]);
    }
}
