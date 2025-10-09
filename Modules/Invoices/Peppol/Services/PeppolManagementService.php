<?php

namespace Modules\Invoices\Peppol\Services;

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
 * Comprehensive Peppol Management Service
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
     * Create and test a new Peppol integration
     */
    public function createIntegration(int $companyId, string $providerName, array $config, ?string $apiToken = null): PeppolIntegration
    {
        DB::beginTransaction();
        
        try {
            $integration = new PeppolIntegration();
            $integration->company_id = $companyId;
            $integration->provider_name = $providerName;
            $integration->api_token = $apiToken; // Will be encrypted automatically by model accessor
            $integration->enabled = false; // Start disabled until tested
            $integration->save();

            // Set configuration using the key-value relationship
            $integration->setConfig($config);

            event(new PeppolIntegrationCreated($integration));

            DB::commit();

            return $integration;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Test connection to a Peppol provider
     */
    public function testConnection(PeppolIntegration $integration): array
    {
        try {
            $provider = ProviderFactory::make($integration);
            
            $result = $provider->testConnection($integration->config);

            // Update integration with test result
            $integration->test_connection_status = $result['ok'] ? PeppolConnectionStatus::SUCCESS : PeppolConnectionStatus::FAILED;
            $integration->test_connection_message = $result['message'];
            $integration->test_connection_at = now();
            $integration->save();

            event(new PeppolIntegrationTested($integration, $result['ok'], $result['message']));

            return $result;
        } catch (\Exception $e) {
            $this->logPeppolError('Peppol connection test failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);

            $integration->test_connection_status = PeppolConnectionStatus::FAILED;
            $integration->test_connection_message = 'Exception: ' . $e->getMessage();
            $integration->test_connection_at = now();
            $integration->save();

            event(new PeppolIntegrationTested($integration, false, $e->getMessage()));

            return [
                'ok' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate a customer's Peppol ID with the provider
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
            $history = new CustomerPeppolValidationHistory();
            $history->customer_id = $customer->id;
            $history->integration_id = $integration->id;
            $history->validated_by = $validatedBy;
            $history->peppol_scheme = $customer->peppol_scheme;
            $history->peppol_id = $customer->peppol_id;
            $history->validation_status = $validationStatus;
            $history->validation_message = $result['present'] ? 'Participant found in network' : 'Participant not found';
            $history->save();

            // Set provider response using the key-value relationship
            if (isset($result['details'])) {
                $history->setProviderResponse($result['details']);
            }

            // Update customer quick-lookup fields
            $customer->peppol_validation_status = $validationStatus;
            $customer->peppol_validation_message = $history->validation_message;
            $customer->peppol_validated_at = now();
            $customer->save();

            event(new PeppolIdValidationCompleted($customer, $validationStatus->value, [
                'history_id' => $history->id,
                'present' => $result['present'],
            ]));

            DB::commit();

            return [
                'valid' => $validationStatus === PeppolValidationStatus::VALID,
                'status' => $validationStatus->value,
                'message' => $history->validation_message,
                'details' => $result['details'],
            ];
        } catch (\Exception $e) {
            if (isset($history)) {
                DB::rollBack();
            }

            $this->logPeppolError('Peppol ID validation failed', [
                'customer_id' => $customer->id,
                'peppol_id' => $customer->peppol_id,
                'error' => $e->getMessage(),
            ]);

            // Save error to history
            $errorHistory = new CustomerPeppolValidationHistory();
            $errorHistory->customer_id = $customer->id;
            $errorHistory->integration_id = $integration->id;
            $errorHistory->validated_by = $validatedBy;
            $errorHistory->peppol_scheme = $customer->peppol_scheme;
            $errorHistory->peppol_id = $customer->peppol_id;
            $errorHistory->validation_status = PeppolValidationStatus::ERROR;
            $errorHistory->validation_message = 'Validation error: ' . $e->getMessage();
            $errorHistory->save();

            return [
                'valid' => false,
                'status' => PeppolValidationStatus::ERROR->value,
                'message' => $e->getMessage(),
                'details' => null,
            ];
        }
    }

    /**
     * Send an invoice to Peppol (queues the job)
     */
    public function sendInvoice(Invoice $invoice, PeppolIntegration $integration, bool $force = false): void
    {
        // Queue the sending job
        SendInvoiceToPeppolJob::dispatch($invoice, $integration, $force);

        $this->logPeppolInfo('Queued invoice for Peppol sending', [
            'invoice_id' => $invoice->id,
            'integration_id' => $integration->id,
        ]);
    }

    /**
     * Get the default/active integration for a company
     */
    public function getActiveIntegration(int $companyId): ?PeppolIntegration
    {
        return PeppolIntegration::where('company_id', $companyId)
            ->where('enabled', true)
            ->where('test_connection_status', PeppolConnectionStatus::SUCCESS)
            ->first();
    }

    /**
     * Auto-suggest Peppol scheme based on customer country
     */
    public function suggestPeppolScheme(string $countryCode): ?string
    {
        $countrySchemeMap = config('invoices.peppol.country_scheme_mapping', []);
        
        return $countrySchemeMap[$countryCode] ?? null;
    }
}
