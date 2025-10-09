<?php

namespace Modules\Invoices\Peppol\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Clients\Models\Relation;
use Modules\Invoices\Events\Peppol\PeppolIdValidationCompleted;
use Modules\Invoices\Events\Peppol\PeppolIntegrationCreated;
use Modules\Invoices\Events\Peppol\PeppolIntegrationTested;
use Modules\Invoices\Models\CustomerPeppolValidationHistory;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Jobs\Peppol\SendInvoiceToPeppolJob;

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
    /**
     * Create and test a new Peppol integration
     */
    public function createIntegration(int $companyId, string $providerName, array $config, ?string $apiToken = null): PeppolIntegration
    {
        DB::beginTransaction();
        
        try {
            $integration = PeppolIntegration::create([
                'company_id' => $companyId,
                'provider_name' => $providerName,
                'config' => $config,
                'api_token' => $apiToken, // Will be encrypted automatically by model accessor
                'enabled' => false, // Start disabled until tested
            ]);

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
            
            $result = $provider->testConnection($integration->config ?? []);

            // Update integration with test result
            $integration->update([
                'test_connection_status' => $result['ok'] ? 'success' : 'failed',
                'test_connection_message' => $result['message'],
                'test_connection_at' => now(),
            ]);

            event(new PeppolIntegrationTested($integration, $result['ok'], $result['message']));

            return $result;
        } catch (\Exception $e) {
            Log::error('Peppol connection test failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);

            $integration->update([
                'test_connection_status' => 'failed',
                'test_connection_message' => 'Exception: ' . $e->getMessage(),
                'test_connection_at' => now(),
            ]);

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
                ? CustomerPeppolValidationHistory::STATUS_VALID
                : CustomerPeppolValidationHistory::STATUS_NOT_FOUND;

            DB::beginTransaction();

            // Save to history
            $history = CustomerPeppolValidationHistory::create([
                'customer_id' => $customer->id,
                'integration_id' => $integration->id,
                'validated_by' => $validatedBy,
                'peppol_scheme' => $customer->peppol_scheme,
                'peppol_id' => $customer->peppol_id,
                'validation_status' => $validationStatus,
                'validation_message' => $result['present'] ? 'Participant found in network' : 'Participant not found',
                'provider_response' => $result['details'],
                'request_payload' => [
                    'scheme' => $customer->peppol_scheme,
                    'id' => $customer->peppol_id,
                ],
            ]);

            // Update customer quick-lookup fields
            $customer->update([
                'peppol_validation_status' => $validationStatus,
                'peppol_validation_message' => $history->validation_message,
                'peppol_validated_at' => now(),
            ]);

            event(new PeppolIdValidationCompleted($customer, $validationStatus, [
                'history_id' => $history->id,
                'present' => $result['present'],
            ]));

            DB::commit();

            return [
                'valid' => $validationStatus === CustomerPeppolValidationHistory::STATUS_VALID,
                'status' => $validationStatus,
                'message' => $history->validation_message,
                'details' => $result['details'],
            ];
        } catch (\Exception $e) {
            if (isset($history)) {
                DB::rollBack();
            }

            Log::error('Peppol ID validation failed', [
                'customer_id' => $customer->id,
                'peppol_id' => $customer->peppol_id,
                'error' => $e->getMessage(),
            ]);

            // Save error to history
            CustomerPeppolValidationHistory::create([
                'customer_id' => $customer->id,
                'integration_id' => $integration->id,
                'validated_by' => $validatedBy,
                'peppol_scheme' => $customer->peppol_scheme,
                'peppol_id' => $customer->peppol_id,
                'validation_status' => CustomerPeppolValidationHistory::STATUS_ERROR,
                'validation_message' => 'Validation error: ' . $e->getMessage(),
                'provider_response' => ['error' => $e->getMessage()],
                'request_payload' => [
                    'scheme' => $customer->peppol_scheme,
                    'id' => $customer->peppol_id,
                ],
            ]);

            return [
                'valid' => false,
                'status' => CustomerPeppolValidationHistory::STATUS_ERROR,
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

        Log::info('Queued invoice for Peppol sending', [
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
            ->where('test_connection_status', 'success')
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
