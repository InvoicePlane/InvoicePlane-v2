<?php

namespace Modules\Invoices\Jobs\Peppol;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Invoices\Enums\PeppolTransmissionStatus;
use Modules\Invoices\Events\Peppol\PeppolTransmissionDead;
use Modules\Invoices\Models\PeppolTransmission;
use Modules\Invoices\Traits\LogsPeppolActivity;

/**
 * Retry failed transmissions with exponential backoff.
 *
 * This job processes transmissions that are scheduled for retry
 * Typically scheduled to run frequently (e.g., every minute)
 */
class RetryFailedTransmissions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use LogsPeppolActivity;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * Process due Peppol transmissions marked for retry and schedule retry attempts.
     *
     * Retrieves up to 50 transmissions with status RETRYING whose next retry time is due,
     * attempts to retry each by delegating to retryTransmission(), logs per-transmission
     * failures without bubbling exceptions, and logs summary information when finished.
     */
    public function handle(): void
    {
        $this->logPeppolInfo('Starting retry failed transmissions job');

        // Get transmissions ready for retry (without global scope since this is a system job)
        $transmissions = PeppolTransmission::withoutGlobalScopes()
            ->where('status', PeppolTransmissionStatus::RETRYING)
            ->where('next_retry_at', '<=', now())
            ->limit(50) // Process in batches
            ->get();

        foreach ($transmissions as $transmission) {
            try {
                $this->retryTransmission($transmission);
            } catch (Exception $e) {
                $this->logPeppolError('Failed to retry transmission', [
                    'transmission_id' => $transmission->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        $this->logPeppolInfo('Completed retry failed transmissions', [
            'retried' => $transmissions->count(),
        ]);
    }

    /**
     * Process a Peppol transmission scheduled for retry, re-dispatching its send job or marking it dead when the retry limit is reached.
     *
     * @param PeppolTransmission $transmission The transmission to evaluate and retry; if its attempts are greater than or equal to the configured `invoices.peppol.max_retry_attempts` it will be marked as dead and a PeppolTransmissionDead event will be fired.
     */
    protected function retryTransmission(PeppolTransmission $transmission): void
    {
        $maxAttempts = config('invoices.peppol.max_retry_attempts', 5);

        if ($transmission->attempts >= $maxAttempts) {
            $transmission->markAsDead('Maximum retry attempts exceeded');
            event(new PeppolTransmissionDead($transmission, 'Maximum retry attempts exceeded'));

            $this->logPeppolWarning('Transmission marked as dead', [
                'transmission_id' => $transmission->id,
                'attempts'        => $transmission->attempts,
            ]);

            return;
        }

        // Dispatch the send job again
        SendInvoiceToPeppolJob::dispatch(
            $transmission->invoice,
            $transmission->integration,
            false, // don't force
            $transmission->id
        );

        $this->logPeppolInfo('Retrying transmission', [
            'transmission_id' => $transmission->id,
            'attempt'         => $transmission->attempts + 1,
        ]);
    }
}
