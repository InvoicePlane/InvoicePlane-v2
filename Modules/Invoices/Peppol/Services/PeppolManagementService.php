<?php

namespace Modules\Invoices\Peppol\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Models\Relation;
use Modules\Invoices\Enums\PeppolConnectionStatus;
use Modules\Invoices\Enums\PeppolValidationStatus;
use Modules\Invoices\Events\Peppol\PeppolIdValidationCompleted;
use Modules\Invoices\Events\Peppol\PeppolIntegrationCreated;
use Modules\Invoices\Events\Peppol\PeppolIntegrationTested;
use Modules\Invoices\Jobs\Peppol\SendInvoiceToPeppolJob;
use Modules\Invoices\Models\CustomerPeppolValidationHistory;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Traits\LogsPeppolActivity;

/**
 * Comprehensive Peppol Management Service.
 *
 * This service provides the main facade for all Peppol operations:
 * - Integration management (create, test, enable/disable)
 * - Customer Peppol ID validation
 * - Invoice sending orchestration
 * - Status checking
 */
class PeppolManagementService
{
    use LogsPeppolActivity;

    /**
     * Create a new Peppol integration for a company, persist its configuration, and emit a creation event.
     *
     * The integration is created disabled (awaiting testing) and its configuration is stored via the
     * shared merchant_clients key-value credential table.
     *
     * @param int   $companyId    the ID of the company that will own the integration
     * @param string $providerName the provider identifier/name for the Peppol integration
     * @param array $config       associative configuration values to attach to the integration
     *
     * @return PeppolIntegration the newly created PeppolIntegration model (initially disabled until tested)
     */
    public function createIntegration(int $companyId, string $providerName, array $config): PeppolIntegration
    {
        DB::beginTransaction();

        try {
            $integration                = new PeppolIntegration();
            $integration->company_id    = $companyId;
            $integration->provider_name = $providerName;
            $integration->enabled       = false; // Start disabled until tested
            $integration->save();

            // Set configuration using the key-value relationship (stores in merchant_clients)
            $integration->setConfig($config);

            event(new PeppolIntegrationCreated($integration));

            DB::commit();

            return $integration;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing Peppol integration's configuration and enabled status.
     *
     * @param PeppolIntegration $integration the integration to update
     * @param array             $config      associative configuration values (replaces existing config)
     * @param bool|null         $enabled     optional enabled status; if null, leaves unchanged
     *
     * @return PeppolIntegration the updated integration
     */
    public function updateIntegration(PeppolIntegration $integration, array $config, ?bool $enabled = null): PeppolIntegration
    {
        DB::beginTransaction();

        try {
            if ($enabled !== null) {
                $integration->enabled = $enabled;
                $integration->save();
            }

            // Update configuration (replaces existing merchant_client rows for this provider)
            // First delete old entries for this provider
            \Modules\Core\Models\MerchantClient::where('company_id', $integration->company_id)
                ->where('driver', $integration->provider_name)
                ->delete();

            // Then insert new entries
            $integration->setConfig($config);

            DB::commit();

            return $integration;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Test connectivity for the given Peppol integration and record the result.
     *
     * Updates the integration's test_connection_status, test_connection_message, and test_connection_at, saves the integration,
     * and dispatches a PeppolIntegrationTested event reflecting success or failure.
     *
     * @param PeppolIntegration $integration the integration to test
     *
     * @return array An array containing:
     *               - `ok` (bool): `true` if the connection succeeded, `false` otherwise.
     *               - `message` (string): A human-readable result or error message.
     */
    public function testConnection(PeppolIntegration $integration): array
    {
        try {
            $provider = ProviderFactory::make($integration);

            $result = $provider->testConnection($integration->config);

            // Update integration with test result
            $integration->test_connection_status  = $result['ok'] ? PeppolConnectionStatus::SUCCESS : PeppolConnectionStatus::FAILED;
            $integration->test_connection_message = $result['message'];
            $integration->test_connection_at      = now();
            $integration->save();

            event(new PeppolIntegrationTested($integration, $result['ok'], $result['message']));

            return $result;
        } catch (Exception $e) {
            $this->logPeppolError('Peppol connection test failed', [
                'integration_id' => $integration->id,
                'error'          => $e->getMessage(),
            ]);

            $integration->test_connection_status  = PeppolConnectionStatus::FAILED;
            $integration->test_connection_message = 'Exception: ' . $e->getMessage();
            $integration->test_connection_at      = now();
            $integration->save();

            event(new PeppolIntegrationTested($integration, false, $e->getMessage()));

            return [
                'ok'      => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate a customer's Peppol identifier against the provider and record the validation history.
     *
     * Performs provider-based validation of the customer's Peppol scheme and ID, persists a
     * CustomerPeppolValidationHistory record (including provider response when available), updates
     * the customer's quick-lookup validation fields, emits a PeppolIdValidationCompleted event,
     * and returns the validation outcome.
     *
     * @param Relation          $customer    the customer relation containing `peppol_scheme` and `peppol_id`
     * @param PeppolIntegration $integration the Peppol integration used to perform validation
     * @param int|null          $validatedBy optional user ID who initiated the validation
     *
     * @return array{
     *   valid: bool,
     *   status: string,
     *   message: string|null,
     *   details: mixed|null
     * } `valid` is `true` when the participant was found; `status` is the validation status value;
     * `message` contains a human-readable validation message or error text; `details` contains
     * optional provider response data when available
     */
    public function validatePeppolId(
        Relation $customer,
        PeppolIntegration $integration,
        ?int $validatedBy = null
    ): array {
        try {
            $provider = ProviderFactory::make($integration);

            // Perform validation
            $result = $provider->validatePeppolId(
                $customer->peppol_scheme,
                $customer->peppol_id
            );

            // Determine validation status
            $validationStatus = $result['present']
                ? PeppolValidationStatus::VALID
                : PeppolValidationStatus::NOT_FOUND;

            DB::beginTransaction();

            // Save to history
            $history                     = new CustomerPeppolValidationHistory();
            $history->customer_id        = $customer->id;
            $history->integration_id     = $integration->id;
            $history->validated_by       = $validatedBy;
            $history->peppol_scheme      = $customer->peppol_scheme;
            $history->peppol_id          = $customer->peppol_id;
            $history->validation_status  = $validationStatus;
            $history->validation_message = $result['present'] ? 'Participant found in network' : 'Participant not found';
            $history->save();

            // Set provider response using the key-value relationship
            if (isset($result['details'])) {
                $history->setProviderResponse($result['details']);
            }

            // Update customer quick-lookup fields
            $customer->peppol_validation_status  = $validationStatus;
            $customer->peppol_validation_message = $history->validation_message;
            $customer->peppol_validated_at       = now();
            $customer->save();

            event(new PeppolIdValidationCompleted($customer, $validationStatus->value, [
                'history_id' => $history->id,
                'present'    => $result['present'],
            ]));

            DB::commit();

            return [
                'valid'   => $validationStatus === PeppolValidationStatus::VALID,
                'status'  => $validationStatus->value,
                'message' => $history->validation_message,
                'details' => $result['details'],
            ];
        } catch (Exception $e) {
            DB::rollBack();

            $this->logPeppolError('Peppol ID validation failed', [
                'customer_id' => $customer->id,
                'peppol_id'   => $customer->peppol_id,
                'error'       => $e->getMessage(),
            ]);

            // Save error to history
            $errorHistory                     = new CustomerPeppolValidationHistory();
            $errorHistory->customer_id        = $customer->id;
            $errorHistory->integration_id     = $integration->id;
            $errorHistory->validated_by       = $validatedBy;
            $errorHistory->peppol_scheme      = $customer->peppol_scheme;
            $errorHistory->peppol_id          = $customer->peppol_id;
            $errorHistory->validation_status  = PeppolValidationStatus::ERROR;
            $errorHistory->validation_message = 'Validation error: ' . $e->getMessage();
            $errorHistory->save();

            return [
                'valid'   => false,
                'status'  => PeppolValidationStatus::ERROR->value,
                'message' => $e->getMessage(),
                'details' => null,
            ];
        }
    }

    /**
     * Queue an invoice to be sent to Peppol.
     *
     * @param Invoice           $invoice     the invoice to send
     * @param PeppolIntegration $integration the Peppol integration to use for sending
     * @param bool              $force       when true, force sending even if the invoice was previously sent or flagged
     */
    public function sendInvoice(Invoice $invoice, PeppolIntegration $integration, bool $force = false): void
    {
        // Queue the sending job
        SendInvoiceToPeppolJob::dispatch($invoice, $integration, $force);

        $this->logPeppolInfo('Queued invoice for Peppol sending', [
            'invoice_id'     => $invoice->id,
            'integration_id' => $integration->id,
        ]);
    }

    /**
     * Retrieve the company's active Peppol integration that is enabled and has a successful connection test.
     *
     * @param int $companyId the company identifier
     *
     * @return PeppolIntegration|null the matching integration, or `null` if none exists
     */
    public function getActiveIntegration(int $companyId): ?PeppolIntegration
    {
        return PeppolIntegration::query()->where('company_id', $companyId)
            ->where('enabled', true)
            ->where('test_connection_status', PeppolConnectionStatus::SUCCESS)
            ->first();
    }

    /**
     * Suggests a Peppol identifier scheme for the given country code.
     *
     * @param string $countryCode the country code (ISO 3166-1 alpha-2)
     *
     * @return string|null the Peppol scheme mapped to the country, or `null` if no mapping exists
     */
    public function suggestPeppolScheme(string $countryCode): ?string
    {
        $countrySchemeMap = config('invoices.peppol.country_scheme_mapping', []);

        return $countrySchemeMap[$countryCode] ?? null;
    }
}
