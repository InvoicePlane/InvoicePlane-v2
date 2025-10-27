<?php

namespace Modules\Invoices\Jobs\Peppol;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Invoices\Events\Peppol\PeppolAcknowledgementReceived;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Traits\LogsPeppolActivity;

/**
 * Poll provider for status updates on sent transmissions.
 *
 * This job checks the status of transmissions that are awaiting acknowledgement
 * Typically scheduled to run periodically (e.g., every 15 minutes)
 */
class PeppolStatusPoller implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use LogsPeppolActivity;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * Polls providers for status updates of recently sent Peppol transmissions and updates local records.
     *
     * Retrieves up to 100 transmissions that are marked SENT, have an external ID, are not yet acknowledged,
     * and were sent more than five minutes ago. For each transmission it checks the provider status, updates
     * the transmission state accordingly, and logs per-transmission errors without aborting the batch.
     *
     * Logs the start and completion of the polling job and includes the number of transmissions checked.
     * /
     * public function handle(): void
     * {
     * $this->logPeppolInfo('Starting Peppol status polling job');
     *
     * // Get all transmissions awaiting acknowledgement
     * $transmissions = PeppolTransmission::where('status', PeppolTransmissionStatus::SENT)
     * ->whereNotNull('external_id')
     * ->whereNull('acknowledged_at')
     * ->where('sent_at', '<', now()->subMinutes(5)) // Allow 5 min grace period
     * ->limit(100) // Process in batches
     * ->get();
     *
     * foreach ($transmissions as $transmission) {
     * try {
     * $this->checkStatus($transmission);
     * } catch (\Exception $e) {
     * $this->logPeppolError('Failed to check transmission status', [
     * 'transmission_id' => $transmission->id,
     * 'error' => $e->getMessage(),
     * ]);
     * }
     * }
     *
     * $this->logPeppolInfo('Completed Peppol status polling', [
     * 'checked' => $transmissions->count(),
     * ]);
     * }
     *
     * /**
     * Polls the external provider for a transmission's delivery status and updates the local record accordingly.
     *
     * Marks the transmission as accepted or rejected based on the provider status, fires a PeppolAcknowledgementReceived
     * event when an acknowledgement payload exists, and persists any provider acknowledgement payload to the transmission.
     *
     * @param PeppolTransmission $transmission the transmission to check and update
     */
    protected function checkStatus(PeppolTransmission $transmission): void
    {
        $provider = ProviderFactory::make($transmission->integration);

        $result = $provider->getTransmissionStatus($transmission->external_id);

        // Update based on status
        $status = mb_strtolower($result['status'] ?? 'unknown');

        if (in_array($status, ['delivered', 'accepted', 'success'])) {
            $transmission->markAsAccepted();
            event(new PeppolAcknowledgementReceived($transmission, $result['ack_payload'] ?? []));

            $this->logPeppolInfo('Transmission accepted', [
                'transmission_id' => $transmission->id,
                'external_id'     => $transmission->external_id,
            ]);
        } elseif (in_array($status, ['rejected', 'failed'])) {
            $transmission->markAsRejected($result['ack_payload']['message'] ?? 'Rejected by recipient');

            $this->logPeppolWarning('Transmission rejected', [
                'transmission_id' => $transmission->id,
                'external_id'     => $transmission->external_id,
            ]);
        }

        // Update provider response
        if (isset($result['ack_payload'])) {
            $transmission->setProviderResponse($result['ack_payload']);
        }
    }
}
