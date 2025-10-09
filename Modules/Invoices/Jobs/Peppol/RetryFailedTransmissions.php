<?php

namespace Modules\Invoices\Jobs\Peppol;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Invoices\Events\Peppol\PeppolTransmissionDead;
use Modules\Invoices\Models\PeppolTransmission;

/**
 * Retry failed transmissions with exponential backoff
 * 
 * This job processes transmissions that are scheduled for retry
 * Typically scheduled to run frequently (e.g., every minute)
 */
class RetryFailedTransmissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(): void
    {
        Log::info('Starting retry failed transmissions job');

        // Get transmissions ready for retry
        $transmissions = PeppolTransmission::where('status', PeppolTransmission::STATUS_RETRYING)
            ->where('next_retry_at', '<=', now())
            ->limit(50) // Process in batches
            ->get();

        foreach ($transmissions as $transmission) {
            try {
                $this->retryTransmission($transmission);
            } catch (\Exception $e) {
                Log::error('Failed to retry transmission', [
                    'transmission_id' => $transmission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Completed retry failed transmissions', [
            'retried' => $transmissions->count(),
        ]);
    }

    /**
     * Retry a single transmission
     */
    protected function retryTransmission(PeppolTransmission $transmission): void
    {
        $maxAttempts = config('invoices.peppol.max_retry_attempts', 5);

        if ($transmission->attempts >= $maxAttempts) {
            $transmission->markAsDead('Maximum retry attempts exceeded');
            event(new PeppolTransmissionDead($transmission, 'Maximum retry attempts exceeded'));
            
            Log::warning('Transmission marked as dead', [
                'transmission_id' => $transmission->id,
                'attempts' => $transmission->attempts,
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

        Log::info('Retrying transmission', [
            'transmission_id' => $transmission->id,
            'attempt' => $transmission->attempts + 1,
        ]);
    }
}
