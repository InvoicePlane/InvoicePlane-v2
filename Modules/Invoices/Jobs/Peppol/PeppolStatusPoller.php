<?php

namespace Modules\Invoices\Jobs\Peppol;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Invoices\Events\Peppol\PeppolAcknowledgementReceived;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Peppol\Providers\ProviderFactory;

/**
 * Poll provider for status updates on sent transmissions
 * 
 * This job checks the status of transmissions that are awaiting acknowledgement
 * Typically scheduled to run periodically (e.g., every 15 minutes)
 */
class PeppolStatusPoller implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(): void
    {
        Log::info('Starting Peppol status polling job');

        // Get all transmissions awaiting acknowledgement
        $transmissions = PeppolTransmission::where('status', PeppolTransmission::STATUS_SENT)
            ->whereNotNull('external_id')
            ->whereNull('acknowledged_at')
            ->where('sent_at', '<', now()->subMinutes(5)) // Allow 5 min grace period
            ->limit(100) // Process in batches
            ->get();

        foreach ($transmissions as $transmission) {
            try {
                $this->checkStatus($transmission);
            } catch (\Exception $e) {
                Log::error('Failed to check transmission status', [
                    'transmission_id' => $transmission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Completed Peppol status polling', [
            'checked' => $transmissions->count(),
        ]);
    }

    /**
     * Check status for a single transmission
     */
    protected function checkStatus(PeppolTransmission $transmission): void
    {
        $provider = ProviderFactory::make($transmission->integration);
        
        $result = $provider->getTransmissionStatus($transmission->external_id);

        // Update based on status
        $status = strtolower($result['status'] ?? 'unknown');

        if (in_array($status, ['delivered', 'accepted', 'success'])) {
            $transmission->markAsAccepted();
            event(new PeppolAcknowledgementReceived($transmission, $result['ack_payload'] ?? []));
            
            Log::info('Transmission accepted', [
                'transmission_id' => $transmission->id,
                'external_id' => $transmission->external_id,
            ]);
        } elseif (in_array($status, ['rejected', 'failed'])) {
            $transmission->markAsRejected($result['ack_payload']['message'] ?? 'Rejected by recipient');
            
            Log::warning('Transmission rejected', [
                'transmission_id' => $transmission->id,
                'external_id' => $transmission->external_id,
            ]);
        }

        // Update provider response
        if (isset($result['ack_payload'])) {
            $transmission->update(['provider_response' => $result['ack_payload']]);
        }
    }
}
